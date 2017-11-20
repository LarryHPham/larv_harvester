<?php

namespace App\Jobs;

use App\Url;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class CrawlUrl extends Job
{
    private $url_parts;
    private $url_model;
    private $a_tag_regex = '/<a[^>]+href=["\']([^"\']+)[\'"]/';
    private $parse_content;

    public function __construct(Url $url, Boolean $parseContent = NULL)
    {
        // Save the model and parts
        $this->url_model = $url;
        $this->url_parts = parse_url($url->article_url);

        // Save the parse flag
        $this->parse_content = $parseContent;
    }

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

    public function failed(Exception $exception)
    {
        // Mark the model as not being crawled
        $this->url_model->curr_scan = 0;
        $this->url_model->num_fail_scans++;
        $this->url_model->save();
    }
}
