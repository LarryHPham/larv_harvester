<?php

namespace App\Jobs;

use App\Url;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

use App\Library\DomUrlParser;

class PageFetcher extends Job
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
     * The flag for parsing for urls as passed into the job
     * @var Boolean
     */
    private $parse_urls;

    /**
     * The job's constructor function. This function saves the parameters passed
     * into the job as class properties
     * @param Url     $url          The URL model to crawl
     * @param Boolean $parseContent Whether the content should be parsed for
     *                              meta-data
     */
    public function __construct(Url $url, $parseContent = True, $parseUrls = True)
    {
        // Save the model and parts
        $this->url_model = $url;
        $this->url_parts = parse_url($url->article_url);

        // Save the parse flag
        $this->parse_content = $parseContent === True;
        $this->parse_urls = $parseUrls === True;
    }

    /**
     * This function executes the main portion of the job. It will grab the URL,
     * parse it for URLs, create jobs for those URLs, and kick off the DOM
     * meta-data parser
     */
    public function handle()
    {
        // Mark the URL as being scraped
        $this->url_model->curr_scan = True;
        $this->url_model->last_crawled = (new Carbon())::now();
        $this->url_model->save();

        // Create a guzzle client
        $client = new GuzzleClient();

        // Request the page
        try {
            $response = $client->request('GET', $this->url_model->article_url, [
                'exceptions' => FALSE,
            ]);
        } catch (\GuzzleHttp\Exception\TooManyRedirectsException $e) {
            $this->url_model->times_scanned++;
            $this->url_model->curr_scan = False;
            $this->url_model->num_fail_scans++;
            $this->url_model->failed_status_code = -1;
            $this->url_model->save();
            return False;
        }

        // Increment the scanned count
        $this->url_model->times_scanned++;
        $this->url_model->curr_scan = False;

        // Check the status code
        switch($response->getStatusCode()) {
            case 200:
                break;
            default:
                $this->url_model->num_fail_scans++;
                $this->url_model->failed_status_code = $response->getStatusCode();
                $this->url_model->save();
                return False;
        }

        // Save the data for the URL
        $this->url_model->save();

        // Get the dom
        $body = (string) $response->getBody();

        // Get the URLs on the page (if needed)
        if ($this->parse_urls) {
            $UrlParser = new DomUrlParser($this->url_model, $body);
            $UrlParser->getLinkedUrls(True, [ // True - restrict to same domain
                'expert_car_reviews',
                'car-news/all-the-latest',
                'car-videos',
                'car-reviews-and-news/top-10',
            ]);
        }

        // TODO: Add an if statement for $this->parse_content to pass to a DOM cacher
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
