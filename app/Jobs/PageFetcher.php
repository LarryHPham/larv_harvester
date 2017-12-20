<?php

namespace App\Jobs;

use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

use App\Url;
use App\CrawlOrder;
use App\Library\DomParser\ParseDom;

class PageFetcher extends Job
{
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
     * The flag for parsing for urls as passed into the job
     * @var Boolean
     */
    private $parse_urls;

    /**
     * The CrawlOrder model to parse next
     * @var CrawlOrder
     */
    private $next_crawl_order;

    /**
     * This function executes the main portion of the job. It will grab the URL,
     * parse it for URLs, create jobs for those URLs, and kick off the DOM
     * meta-data parser
     */
    public function handle()
    {
        // Always dispatch another job
        dispatch(new PageFetcher());
        // Get the next URL to crawl
        $this->next_crawl_order = CrawlOrder::getNextUrl();
        // Check for a next crawl
        if ($this->next_crawl_order === null) {
            return true;
        }

        // Save the claim
        $this->next_crawl_order->claimed_at = \Carbon\Carbon::now();
        $this->next_crawl_order->save();
        // Get the URL model
        $this->url_model = $this->next_crawl_order->urlModel;
        $this->parse_content = $this->next_crawl_order->get_content;
        $this->parse_urls = $this->next_crawl_order->get_urls;

        // Mark the URL as being scraped
        $this->url_model->curr_scan = true;
        $this->url_model->last_crawled = (new Carbon())::now();
        $this->url_model->save();

        // Create a guzzle client
        $client = new GuzzleClient();

        // Request the page
        try {
            $response = $client->request('GET', $this->url_model->article_url, [
                'exceptions' => false,
            ]);
        } catch (\GuzzleHttp\Exception\TooManyRedirectsException $e) {
            $this->markFailed(1);
            return false;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->markFailed(-2);
            return false;
        }

        // Check the status code
        switch ($response->getStatusCode()) {
            case 200:
                break;
            default:
                $this->markFailed($response->getStatusCode());
                return false;
        }

        // Increment the scanned count
        $this->url_model->times_scanned++;
        $this->url_model->curr_scan = false;

        // Save the data for the URL
        $this->url_model->save();

        // Get the dom
        $body = (string) $response->getBody();

        // Get the URLs on the page (if needed)
        if ($this->parse_urls) {
            // Determine which parser to use
            if (parse_url($this->url_model->article_url)['host'] === 'rss.kbb.com' || preg_match('/\.xml$/', $this->url_model->article_url) === 1) {
                $UrlParser = new \App\Library\UrlParser\XmlUrlParser($this->url_model, $body);
            } else {
                $UrlParser = new \App\Library\UrlParser\DomUrlParser($this->url_model, $body);
            }

            $UrlParser->getLinkedUrls(true, [ // true - restrict to same domain
                'expert_car_reviews',
                'car-news/all-the-latest',
                'car-videos',
                'car-reviews-and-news/top-10',
            ]);
        }

        // Call to the DomParser
        if ($this->parse_content) {
            $parser = dispatch(
              new ParseDom($this->url_model, $body)
            );
            if (!is_null($parser)) {
                // Save the parser used
                $this->url_model->parsed_by = $parser;
                $this->url_model->save();
            }
        }

        // Delete from the table
        $this->next_crawl_order->delete();
    }

    /**
     * Mark the URL crawl as failed
     * @param  Integer $code The failure code
     */
    private function markFailed($code)
    {
        // TODO Need to come back to this since when an error occurs the url_model is null in this function
        if (is_null($this->url_model)) {
            return;
        }
        $this->url_model->times_scanned++;
        $this->url_model->curr_scan = false;
        $this->url_model->num_fail_scans++;
        $this->url_model->failed_status_code = $code;
        $this->url_model->save();

        // Re-crawl or not (only curl errors and only try 5 times)
        if ($code > 0 || $this->url_model->num_fail_scans > 5) {
            $this->next_crawl_order->delete();
        } else {
            // Decrease the priority
            $this->next_crawl_order->weight = round($this->next_crawl_order->weight / 2);
            $this->next_crawl_order->claimed_at = null;
            $this->next_crawl_order->save();
        }
    }

    /**
     * This function runs if the job fails and sets curr_scan to false and
     * increments the number of failed scans.
     * @param  Exception $exception The exeception that occured
     */
    public function failed(\Exception $exception)
    {
        // Mark the model as not being crawled
        $this->markFailed(-5);
    }
}
