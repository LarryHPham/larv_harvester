<?php

namespace App\Library\DomParser;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use GuzzleHttp\Client as GuzzleClient;

use App\Url;
use App\Ledger;
use App\Library\StorageCache;

class ParseDom
{
    /**
     * The parsers that will be tested in the order they should be tested to
     * parse the content given
     * @var Array
     */
    private $registered_parsers = [
        '\App\Library\DomParser\KBB\CarArticleFormat1',
        '\App\Library\DomParser\KBB\CarArticleFormat2',
        '\App\Library\DomParser\KBB\CarArticleFormat3',
        '\App\Library\DomParser\KBB\CarArticleFormat4',
        '\App\Library\DomParser\KBB\ExpertCarReviewFormat1',
        '\App\Library\DomParser\KBB\ExpertCarReviewFormat2',
        '\App\Library\DomParser\KBB\RichTextCarousel',
        '\App\Library\DomParser\KBB\RichTextOneImage',
        '\App\Library\DomParser\KBB\RichTextVideo',
        '\App\Library\DomParser\KBB\ListPage',
        '\App\Library\DomParser\KBB\ListVideoPage',
        '\App\Library\DomParser\KBB\VideoPage',
    ];

    /**
     * The path is relative path in which the json file will be saved in
     * @var Object
     */
    public $json_path;

    /**
     * This variable holds the object that will be written into the article JSON
     * @var Object
     */
    public $json = false;

    /**
     * The parser that was used to get the object
     * @var String
     */
    public $parser_used = null;

    /**
     * This class loops over the registered parsers and determines which (if
     * any) should be used to parse the URL
     * @param Url    $url     The model of the URL to crawl
     * @param String $content The results of the crawler
     */
    public function __construct(Url $url, $content)
    {
        // Parse the DOM
        $parsed_dom = new DomCrawler($content);

        // Determine which (if any) parser to use
        foreach ($this->registered_parsers as $test_parser) {
            $parser = new $test_parser($url, $parsed_dom);

            // Determine if the given parser is valid
            if ($parser->valid) {
                $this->parser_used = $test_parser;
                break;
            }
        }

        // If there was no parser found, exit
        if ($this->parser_used === null) {
            return;
        }

        // Get the JSON object
        $this->json = $parser->getValues();
        $json_data = json_decode($this->json);
        // Create path of object
        // NOTE: we are using publisher as the folder directory from json data
        $publisher = $json_data->publisher;

        // json file path with root path plus an array of keywords to create full url
        $path_array = [];
        array_push(
            $path_array,
            env('AWS_ARTICLE_JSON_ROOT_PATH'),
            $publisher,
            $url->article_hash.'.json'
        );
        $json_file_path = implode($path_array, '/');

        // create JSON file and store on aws server
        $json_storage = new StorageCache(env('JSON_CACHE'));
        $json_storage->cacheContent($json_file_path, $this->json);

        // Save the values into elastic-search
        $ledger = Ledger::updateOrCreate(
          ['url_hash' => $url->article_hash],
          [
            'article_url' => $url->article_url,
            'path_to_file' => $json_file_path
          ]
        );
        // Ledger returns id to be used to call elastic search api
        // check if the Ledger has updated or created by checking the elastic search index id and updated date to know whether to create new entry with ES post or update entry with put
        $client = new GuzzleClient();
        $post_json = ['id' => $ledger->id];
        $header_options = ['Content-Type' => 'application/json'];
        if ($ledger->elastic_index_id === null) {
            $response = $client->post(env('ES_FQDN'), [
              'headers' => $header_options,
              'json' => $post_json,
            ]);
        } else {
            // $response = $client->put(env('ES_FQDN'), [
            //   'headers' => $header_options,
            //   'json' => $post_json,
            // ]);
        }
    }
}
