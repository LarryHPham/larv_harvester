<?php

namespace App\Library;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use App\Url;
use App\Jobs\PageFetcher;

class XmlUrlParser extends BaseParser
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
        // Initialize the variables for links
        $page_links = [];
        $link_texts = [];

        // Loop over the tags
        $this
            ->parsed_dom
            ->filterXPath("//*[name()='feedburner:origLink']")
            ->each(function($node) use (&$page_links, &$link_texts, $RestrictToSameDomain) {
                // Get the url
                $url = $node->text();

                // Check for null
                if ($url === NULL) {
                    return False;
                }

                // Parse the URL
                $url = $this->parseFoundUrl($url, False);

                // Check for false or non-kbb domain
                if ($url === False || ($RestrictToSameDomain && parse_url($url)['host'] !== 'www.kbb.com')) {
                    return False;
                }

                // Add to the text array
                $link_texts[$url] = '{{XML Link}}';

                // Add to the page links
                $page_links[] = $url;
            });

        // Insert and update the found links
        $this->insertOrUpdateLinks($page_links, $link_texts, $WhitelistPatterns);

        // Return a success boolean
        return True;
    }
}
