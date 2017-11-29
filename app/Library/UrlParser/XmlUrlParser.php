<?php

namespace App\Library\UrlParser;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use App\Url;
use App\Jobs\PageFetcher;

class XmlUrlParser extends BaseParser
{
    /**
     * An array of the links on a page
     * @var Array
     */
    private $page_links = [];

    /**
     * The text in each link
     * @var Array
     */
    private $link_texts = [];

    /**
     * The weight to add to each link
     * @var Array
     */
    protected $link_weights = 1;

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
        // Check a variety of tags
        $Complete = $this->parseFeedburnerTags($RestrictToSameDomain) || $this->parseSitemapTags($RestrictToSameDomain) || $this->parseUrlTags($RestrictToSameDomain);

        // Insert and update the found links
        $this->insertOrUpdateLinks($this->page_links, $this->link_texts, $WhitelistPatterns);

        // Return a success boolean
        return True;
    }

    /**
     * Parse the DOM for tags using the feedburner class name (RSS feeds)
     * @param  Boolean $RestrictToSameDomain Keep on www.kbb.com or not
     * @return Boolean                       Were tags found?
     */
    private function parseFeedburnerTags($RestrictToSameDomain)
    {
        // Get the tags
        $tags = $this
            ->parsed_dom
            ->filterXPath("//*[name()='feedburner:origLink']");

        // Check the size
        if (sizeof($tags) === 0) {
            return False;
        }

        // Parse the values
        $this->parseNodes($tags, $RestrictToSameDomain);
        return True;
    }

    /**
     * Parse the DOM for tags using the sitemap class name (List of sitemaps)
     * @param  Boolean $RestrictToSameDomain Keep on www.kbb.com or not
     * @return Boolean                       Were tags found?
     */
    private function parseSitemapTags($RestrictToSameDomain)
    {
        // Get the tags
        $tags = $this
            ->parsed_dom
            ->filter('sitemap > loc');

        // Check the size
        if (sizeof($tags) === 0) {
            return False;
        }

        // Increase the weight added to sitemaps
        $this->link_weights = 19;

        // Parse the values
        $this->parseNodes($tags, $RestrictToSameDomain);
        return True;
    }

    /**
     * Parse the DOM for tags using the url class name (List of sitemaps)
     * @param  Boolean $RestrictToSameDomain Keep on www.kbb.com or not
     * @return Boolean                       Were tags found?
     */
    private function parseUrlTags($RestrictToSameDomain)
    {
        // Get the tags
        $tags = $this
            ->parsed_dom
            ->filter('url > loc');

        // Check the size
        if (sizeof($tags) === 0) {
            return False;
        }

        // Parse the values
        $this->parseNodes($tags, $RestrictToSameDomain);
        return True;
    }

    /**
     * Process nodes to get the URLs to crawl
     * @param  Array   $nodeList             The nodes to parse
     * @param  Boolean $RestrictToSameDomain Keep on www.kbb.com or not
     */
    private function parseNodes($nodeList, $RestrictToSameDomain)
    {
        $nodeList
            ->each(function($node) use ($RestrictToSameDomain) {
                // Get the url
                $url = $node->text();

                // Check for null
                if ($url === NULL) {
                    return False;
                }

                // Parse the URL
                $url = $this->parseFoundUrl($url, False);

                // Check for false or non-kbb domains
                if ($url === False || ($RestrictToSameDomain && parse_url($url)['host'] !== 'www.kbb.com')) {
                    return False;
                }

                // Add to the text array
                $this->link_texts[$url] = '{{XML Link}}';

                // Add to the page links
                $this->page_links[] = $url;
            });
    }
}
