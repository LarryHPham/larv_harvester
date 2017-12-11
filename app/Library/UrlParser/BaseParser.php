<?php

namespace App\Library\UrlParser;

use App\Url;

class BaseParser
{
    /**
     * The results of running `parse_url` on the URL model's URL. This is saved
     * here to avoid running the function repeatedly
     * @var Object
     */
    protected $url_parts;

    /**
     * The URL model that was passed into the job
     * @var Url
     */
    protected $url_model;

    /**
     * The result of the DomCrawler
     * @var Dom
     */
    protected $parsed_dom;

    /**
     * This array dictates how often URLs should be re-crawled. If a URL is not
     * mentioned, it is assumed to be not recrawled
     * @var Array
     */
    protected $link_recrawl = [];

    /**
     * The query parameters that can be safely removed from the URL
     * @var Array
     */
    protected $ignorable_parameters = [
        'vehicleclass',
        'intent',
        'LNX', // Used for instant cash offer
        'Lp', // Used for instant cash offer
    ];

    /**
     * The constructor function saves the URL model and parses the DOM string
     * @param Url    $url       The model of the URL that the dom belongs to
     * @param String $DomString The string found when crawling the DOM
     */
    public function __construct(Url $url)
    {
        // Save the URL and parts
        $this->url_model = $url;
        $this->url_parts = parse_url($url->article_url);
    }

    /**
     * This function parses a URL and makes sure it is valid. It will also add
     * domains to URLs that begin with '/' and protocol to URLs that begin with
     * '//'.
     * @param  String  $href        The URL to parse
     * @param  Boolean $CheckDomain Whether to check against the crawled URLs
     *                              domain
     * @return Boolean              false if the URL should not be stored, the
     *                              url otherwise
     */
    protected function parseFoundUrl(String $href, $CheckDomain)
    {
        // Filter out anything that doesn't start with a slash, http, or https
        if (
            substr($href, 0, 1) !== '/' &&
            substr($href, 0, 4) !== 'http' &&
            substr($href, 0, 5) !== 'https'
        ) {
            return false;
        }

        // Modify the / URLs with the domains
        if (substr($href, 0, 2) === '//') {
            $href = $this->url_parts['scheme'] . ':' . $href;
        } elseif (substr($href, 0, 1) === '/' && substr($href, 0, 2) !== '//') {
            $href = $this->url_parts['scheme'] . '://' . $this->url_parts['host'] . $href;
        }

        // Get the parts of the URL
        $url_parts = parse_url($href);

        // Make sure the URL is on the same domain
        if ($CheckDomain && parse_url($href, PHP_URL_HOST) !== $this->url_parts['host']) {
            return false;
        }

        // Build the base URL
        $href = $url_parts['scheme'] . '://' . $url_parts['host'];

        // Add the components (if available)
        if (isset($url_parts['path'])) {
            // Remove the trailing slash from a path if exists
            $href .= preg_replace('/\/+$/', '', $url_parts['path']);
        }
        if (isset($url_parts['query'])) {
            // Parse the components
            parse_str($url_parts['query'], $parameters);

            // Unset the unused parameters
            foreach ($this->ignorable_parameters as $param) {
                unset($parameters[$param]);
            }

            // Determine if there are enough parts left for a string
            if (sizeof($parameters) > 0) {
                // Sort the parameters
                ksort($parameters);

                // Add to the URL
                $href .= '?' . http_build_query($parameters);
            }
        }

        // Determine if the URL matches the current URL
        if ($href === $this->url_model->article_url) {
            return false;
        }

        return $href;
    }

    /**
     * This function determines whether the URL should be crawled. The URL is
     * labelled as "should crawl" if it matches one of the WhiteListPatterns or
     * it is next to the root level of the domain.
     * @param  String $url               The URL to decide on
     * @param  Array  $WhitelistPatterns An array of strings that, if the url
     *                                   contains any of them, should mark the
     *                                   url as "should crawl"
     * @return Boolean                   true if the URL should be crawled, else
     *                                   false
     */
    protected function shouldCrawlUrl(String $url, $WhitelistPatterns = [])
    {
        // Check the patterns
        if (
            sizeof($WhitelistPatterns) === 0 || // If there are no patterns then crawl the URL
            str_contains($url, $WhitelistPatterns) // If it matches a pattern crawl it
        ) {
            return true;
        }

        return false;
    }

    /**
     * This function inserts and crawls new links and adds connections between
     * pages for old links
     * @param  Array $page_links The links found on the page
     */
    protected function insertOrUpdateLinks($page_links, $WhitelistPatterns)
    {
        // Sort the links and remove duplicates
        arsort($page_links);
        $page_links = array_unique($page_links);

        // Insert or update the URLs
        foreach ($page_links as $found_link) {
            // Insert the URL if it doesn't already exist
            try {
                $new_url = Url::findByHash($found_link);
                if ($new_url === null) {
                    // Create the URL
                    $new_url = new Url([
                        'article_url' => $found_link,
                        'active_crawl' => $this->shouldCrawlUrl($found_link, $WhitelistPatterns),
                        'created_at' => $this->url_model->last_crawled,
                    ]);
                    $new_url->save();

                    $new_url
                        ->priority()
                        ->create([]);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $new_url = Url::findByHash($found_link);
                if ($new_url === null) {
                    continue;
                }
            }

            // Increment the weight
            if ($new_url->priority !== null) {
                $new_url->priority->weight++;

                // Check for additional weighting
                if (isset($this->link_weights)) {
                    $new_url->priority->weight += $this->link_weights;
                }

                $new_url->priority->save();
            }

            // Set up the re-crawl interval (if needed)
            if (isset($this->link_recrawl[$found_link])) {
                $new_url->recrawl_interval = $this->link_recrawl[$found_link];
                $new_url->save();
            }

            // Create the relationship
            $this
                ->url_model
                ->articleLinksTo()
                ->save($new_url);
        }
    }
}
