<?php

namespace App\Jobs;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

//internal class import
use App\Url;
use App\CrawlOrder;
use App\Library\DomParser\ParseDom;

use App\Library\PhantomJsUrlContentRenderer;
use App\Library\StorageCache;

class PageParser extends Job
{
    /**
     * This function executes the main portion of the job. It will grab the URL,
     * parse it for URLs, create jobs for those URLs, and kick off the DOM
     * meta-data parser
     */
    private $entry_url;

    /**
     * The URL model that was passed into the job
     * @var Url
     */
    private $url_model;

    /**
     * The parse_content is a flag in the database to determine if content needs to be parsed
     * @var INT
     */
    private $parse_content;

    public function __construct($url)
    {
        $this->entry_url = $url;
    }

    public function handle()
    {
        $this->url_model = URL::findByHash($this->entry_url);
        $this->parse_content = 1; // TODO use flag in the database
        $hash_entry_url = Url::createHash($this->entry_url);
        $phantom = false;

        // DECIDE WHETHER TO USE PHANTOM JS OR GUZZLE Client
        /* NOTE:
         * PHANTOMJS will allow javascript to render
         * at the cost of javascript render time (5 sec)
         * GUZZLE is server to server request for content
         * thus direct and fast (200 ms);
        **/
        if ($phantom) {
            $client = new PhantomJsUrlContentRenderer();

            $response = $client->renderContentFromUrl($this->entry_url);

            $body = $response['content'];
        } else {
            $client = new GuzzleClient();
            // Request the page
            try {
                $response = $client->request('GET', $this->entry_url, [
                      'exceptions' => false,
                    ]);
            } catch (\GuzzleHttp\Exception\TooManyRedirectsException $e) {
                return false;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                return false;
            }

            // Check the status code
            switch ($response->getStatusCode()) {
                    case 200:
                        break;
                    default:
                        return false;
                }
            $body = (string) $response->getBody();
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

        // Cached Data is now stored in local variable removal of cached data is done here since it is no longer needed
        // $temp_storage->removeCachedData($hash_entry_url);
    }

 
}
