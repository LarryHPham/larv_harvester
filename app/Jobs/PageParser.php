<?php

namespace App\Jobs;

use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

//internal class import
use App\Url;
use App\Library\DomCache;
use App\Library\DomDataParser;

class PageParser extends Job
{
    private $entry_url;
    /**
     * This function executes the main portion of the job. It will grab the URL,
     * parse it for URLs, create jobs for those URLs, and kick off the DOM
     * meta-data parser
     */

    public function __construct($url)
    {
        $this->entry_url = $url;
    }

    public function handle()
    {
        print("-----------------------STARTED-------------------------------\n");
        print("STARTING PARSE: $this->entry_url \n");
        // Check if Cache String exists Dom by sending in url as hash md5 then decided whether to cache or not
        $hash_entry_url = Url::createHash($this->entry_url);
        $cache_storage = new DomCache();
        print("ENTRY URL HASH: $hash_entry_url \n");
        if (!$cache_storage->CheckCachedData($hash_entry_url)) {
            // Create a guzzle client
            $client = new GuzzleClient();

            // Request the page
            $response = $client->request('GET', $this->entry_url, [
              'exceptions' => false,
          ]);

            // Check the status code
            switch ($response->getStatusCode()) {
              case 200:
                  break;
              default:
                  $this->markFailed($response->getStatusCode());
                  return false;
          }

            // get Guzzle Body content and cache the data
            $body = (string) $response->getBody();
            $cache_storage->cacheContent($hash_entry_url, $body);
        } else {
            $body = $cache_storage->getCacheData($hash_entry_url);
        }


        // Get the dom




        // TODO: Add an if statement for $this->parse_content to pass to a DOM cacher
        // dispatch new job to parse out the cached data and the hash
        // if ($this->parse_content) {
        dispatch(new DomDataParser($this->entry_url, $body));
        // }

        // Cached Data is now stored in local variable removal of cached data is done here since it is no longer needed
        $cache_storage->removeCachedData($hash_entry_url);
        print("------------------------END------------------------------\n");
    }
}
