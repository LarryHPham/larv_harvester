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
    private $RegisteredParsers = [
        '\App\Library\DomParser\KBB\CarArticle',
        '\App\Library\DomParser\KBB\ExpertCarReview',
        '\App\Library\DomParser\KBB\RichTextCarousel',
        '\App\Library\DomParser\KBB\RichTextOneImage',
        '\App\Library\DomParser\KBB\RichTextVideo',
        '\App\Library\DomParser\KBB\ListPage',
        '\App\Library\DomParser\KBB\VideoPage',
    ];

    /**
     * This variable holds the object that will be written into the article JSON
     * @var Object
     */
    public $json = false;

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
        $found_parser = false;
        foreach ($this->RegisteredParsers as $test_parser) {
            $parser = new $test_parser($url, $parsed_dom);

            // Determine if the given parser is valid
            if ($parser->valid) {
                $found_parser = true;
                print $test_parser . "\n";
                break;
            }
        }

        // If there was no parser fonud, exit
        if (!$found_parser) {
            print "No Parser\n";
            return;
        }

        // Get the JSON object
        $this->json = $parser->getValues();
    }
}
