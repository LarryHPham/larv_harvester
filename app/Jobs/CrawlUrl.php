<?php

namespace App\Jobs;

use App\Url;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CrawlUrl extends Job
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
     * The flag for parsing content as passed into the job
     * @var Boolean
     */
    private $parse_content;

    /**
     * The job's constructor function. This function saves the parameters passed
     * into the job as class properties
     * @param Url     $url          The URL model to crawl
     * @param Boolean $parseContent Whether the content should be parsed for
     *                              meta-data
     */
    public function __construct(Url $url, Boolean $parseContent = NULL)
    {
        // Save the model and parts
        $this->url_model = $url;
        $this->url_parts = parse_url($url->article_url);

        // Save the parse flag
        $this->parse_content = $parseContent;
    }

    /**
     * This function executes the main portion of the job. It will grab the URL,
     * parse it for URLs, create jobs for those URLs, and kick off the DOM
     * meta-data parser
     */
    public function handle()
    {
        // Check for it already being scraped
        if ($this->url_model->curr_scan) {
            print('URL is already being scanned' . "\n");
            return;
        }

        // Mark the URL as being scraped
        $this->url_model->curr_scan = True;
        $this->url_model->last_crawled = (new Carbon())::now();
        $this->url_model->save();

        // Create a guzzle client
        $client = new GuzzleClient();

        // Request the page
        $response = $client->request('GET', $this->url_model->article_url);

        // Increment the scanned count
        $this->url_model->times_scanned++;
        $this->url_model->curr_scan = False;

        // Check the status code
        switch($response->getStatusCode()) {
            case 200:
                break;
            default:
                var_dump($response->getStatusCode());
                $this->url_model->num_fail_scans++;
                $this->url_model->save();
                return False;
        }

        // Save the data for the URL
        $this->url_model->save();

        // Get the dom
        $body = (string) $response->getBody();

        // Get the URLs on the page
        $found_urls = $this->getLinkedUrls($body);

        // Determine if the URL already exists
        foreach ($found_urls as $found_url) {
            // Insert the URL if it was just found
            $new_url = Url::findByHash($found_url);
            if ($new_url === NULL) {
                // Create the URL
                $new_url = new Url([
                    'article_url' => $found_url,
                ]);
                $new_url->save();

                // Create the job
                dispatch(new CrawlUrl($new_url));
            }

            // Create the relationship
            $this
                ->url_model
                ->articleLinksTo()
                ->save($new_url);
        }

        // Mark the URL as not being scraped
        $this->url_model->save();
    }

    /**
     * This function parses a string DOM for URLs using the Symphony DOM Crawler
     * @param  String $body The DOM to parse for URLs
     * @return Array        The URLs that were found in the DOM
     */
    private function getLinkedUrls(String $body)
    {
        // Create a dom object to use
        $dom = new DomCrawler($body);

        // Initialize the page links variable to pass into the closure
        $page_links = [];

        // Loop over the a tags
        $dom
            ->filter('a')
            ->each(function ($node) use (&$page_links) {
                // Get the href
                $href = $node->attr('href');

                // Check for null
                if ($href === NULL) {
                    return False;
                }

                // Parse the URL
                $href = $this->parseFoundUrl($href);

                // Check for a falsey value
                if ($href === False) {
                    return False;
                }

                // Add to the page_links array
                $page_links[] = $href;
            });

        // Sort the links
        arsort($page_links);

        // Remove the duplicates
        $page_links = array_unique($page_links);

        // Return the URLs
        return $page_links;
    }

    /**
     * This function parses a URL and makes sure it is valid. It will also add
     * domains to URLs that begin with a slash and protocol to URLs that begin
     * with //
     * @param  String         $href The URL to parse
     * @return String/Boolean       The parsed href or False if the href is not
     *                              valid
     */
    private function parseFoundUrl(String $href)
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

        // Get rid of the trailing slash
        $href = preg_replace('/\/$/', '', $href);

        // Get rid of the query string
        $href = preg_replace('/\?[^\/]*$/', '', $href);

        // Determine if the URL matches the current URL
        if ($href === $this->url_model->article_url) {
            return False;
        }

        // Make sure the URL is on the same domain
        if (strpos($href, $this->url_parts['host']) === False) {
            return False;
        }

        return $href;
    }

    /**
     * This function runs if the job fails and sets curr_scan to False and
     * increments the number of failed scans.
     * @param  Exception $exception The exeception that occured
     */
    public function failed(Exception $exception)
    {
        // Mark the model as not being crawled
        $this->url_model->curr_scan = False;
        $this->url_model->num_fail_scans++;
        $this->url_model->save();
    }
}
