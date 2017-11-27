<?php

namespace App\Library;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use App\Url;
use App\Jobs\PageFetcher;

class DomUrlParser extends BaseParser
{
    /**
     * The constructor function saves the URL model and parses the DOM string
     * @param Url    $url       The model of the URL that the dom belongs to
     * @param String $DomString The string found when crawling the DOM
     */
    public function __construct(Url $url, $DomString)
    {
        // Save the URL and parts
        parent::__construct($url);

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

        // Insert and update the found links
        $this->insertOrUpdateLinks($page_links, $link_texts, $WhitelistPatterns);

        // Return a success boolean
        return True;
    }
}
