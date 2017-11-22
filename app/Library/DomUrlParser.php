<?php

namespace App\Library;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use App\Url;
use App\Jobs\PageFetcher;
use Log;

class DomUrlParser
{
    /**
     * The results of running `parse_url` on the URL model's URL. This is saved
     * here to avoid running the function repeatedly
     * @var Object
     */
    private $url_parts;

    /**
     * The URL model that was passed into the job
     * @var Url
     */
    private $url_model;

    /**
     * The result of the DomCrawler
     * @var Dom
     */
    private $parsed_dom;

    /**
     * The constructor function saves the URL model and parses the DOM string
     * @param Url    $url       The model of the URL that the dom belongs to
     * @param String $DomString The string found when crawling the DOM
     */
    public function __construct(Url $url, String $DomString)
    {
        // Save the URL and parts
        $this->url_model = $url;
        $this->url_parts = parse_url($url->article_url);

        // Parse the dom and save it
        $this->parsed_dom = new DomCrawler($DomString);
    }

    /**
     * This function parses the DOM for linked URLs and inserts them into the
     * database
     * @param  Boolean $RestrictToSameDomain Whether to only crawl URLs that are
     *                                       on the same domain as the original
     *                                       url
     * @return Boolean                       Success indicator
     */
    public function getLinkedUrls($RestrictToSameDomain = True, $WhitelistPatterns = [])
    {
        // Initialize the variable for links
        $page_links = [];
        $link_texts = [];

        // Loop over the a tags
        $this
            ->parsed_dom
            ->filter('a')
            ->each(function($node) use (&$page_links, &$link_texts, $RestrictToSameDomain) {
                // Get the href attribute
                $href = $node->attr('href');

                // Check for null
                if ($href === NULL) {
                    return False;
                }

                // Parse the URL
                $href = $this->parseFoundUrl($href, $RestrictToSameDomain);

                // Check for false (invalid href)
                if ($href === False) {
                    return False;
                }

                // Add to the text array
                $link_texts[$href] = $node->text();

                // Add to the page_links array
                $page_links[] = $href;
            });

        // Sort the links and remove duplicates
        arsort($page_links);
        $page_links = array_unique($page_links);

        // Insert or update the URLs
        foreach ($page_links as $found_link) {
            // Insert the URL if it doesn't already exist
            try {
                $new_url = Url::findByHash($found_link);
                if ($new_url === NULL) {
                    // Create the URL
                    $new_url = new Url([
                        'article_url' => $found_link,
                        'active_crawl' => $PatternMatch,
                    ]);
                    $new_url->save();

                    // Dispatch the job if needed
                    if ($this->shouldCrawlUrl($found_link, $WhitelistPatterns)) {
                        dispatch(new PageFetcher($new_url));
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $new_url = Url::findByHash($found_link);
                if ($new_url === NULL) {
                    continue;
                }
            }

            // Create the relationship
            $this
                ->url_model
                ->articleLinksTo()
                ->save($new_url, [
                    'link_text' => trim($link_texts[$found_link]),
                ]);
        }

        // Return a success boolean
        return True;
    }

    /**
     * This function parses a URL and makes sure it is valid. It will also add
     * domains to URLs that begin with '/' and protocol to URLs that begin with
     * '//'.
     * @param  String  $href        The URL to parse
     * @param  Boolean $CheckDomain Whether to check against the crawled URLs
     *                              domain
     * @return Boolean              False if the URL should not be stored, the
     *                              url otherwise
     */
    private function parseFoundUrl(String $href, $CheckDomain)
    {
        // Filter out anything that doesn't start with a slash, http, or https
        if (
            substr($href, 0, 1) !== '/' &&
            substr($href, 0, 4) !== 'http' &&
            substr($href, 0, 5) !== 'https'
        ) {
            return False;
        }

        // Modify the / URLs with the domains
        if (substr($href, 0, 2) === '//') {
            $href = $this->url_parts['scheme'] . ':' . $href;
        } else if (substr($href, 0, 1) === '/' && substr($href, 0, 2) !== '//') {
            $href = $this->url_parts['scheme'] . '://' . $this->url_parts['host'] . $href;
        }

        // Get rid of the hash
        $href = preg_replace('/#[^\/]*$/', '', $href);

        // Get rid of the query string
        $href = preg_replace('/\?[^\/]*$/', '', $href);

        // Get rid of the trailing slash
        $href = preg_replace('/\/$/', '', $href);

        // Determine if the URL matches the current URL
        if ($href === $this->url_model->article_url) {
            return False;
        }

        // Make sure the URL is on the same domain
        if ($CheckDomain && parse_url($href)['host'] !== $this->url_parts['host']) {
            return False;
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
     * @return Boolean                   True if the URL should be crawled, else
     *                                   False
     */
    private function shouldCrawlUrl(String $url, $WhitelistPatterns = [])
    {
        // Check the patterns
        if (
            sizeof($WhitelistPatterns) === 0 || // If there are no patterns then crawl the URL
            str_contains($url, $WhitelistPatterns) // If it matches a pattern crawl it
        ) {
            return True;
        }

        // Check for the URL being a top level URL
        $url_parts = parse_url($url);
        return substr_count($url_parts['path'], '/') <= 1;
    }
}
