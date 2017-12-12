<?php

namespace App\Library\DomParser;

use App\Url;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

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
        // TODO set path to an ENV file that pushes to location on database
        $this->json_path = storage_path('app/json')."/";

        // Parse the DOM
        $parsed_dom = new DomCrawler($content);

        // Determine which (if any) parser to use
        foreach ($this->registered_parsers as $test_parser) {
            $parser = new $test_parser($url, $parsed_dom);

            // Determine if the given parser is valid
            if ($parser->valid) {
                print("FOUND PARSER Constructing: $test_parser \n");
                $this->parser_used = $test_parser;
                break;
            }
        }

        // If there was no parser fonud, exit
        if ($this->parser_used === null) {
            print "No Parser\n";
            return;
        }

        // Get the JSON object
        $this->json = $parser->getValues();

        // create JSON file
        $parser->createJsonFile(
          $this->json_path.$url->article_hash.'.json',
          $this->json
        );

        // @TODO Save the values into elastic-search
    }
}
