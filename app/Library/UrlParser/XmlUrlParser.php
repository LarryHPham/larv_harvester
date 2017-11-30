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
    public function getLinkedUrls($RestrictToSameDomain = true, $WhitelistPatterns = [])
    {
        // Check a variety of tags
        $Complete = $this->parseFeedburnerTags($RestrictToSameDomain) || $this->parseSitemapTags($RestrictToSameDomain) || $this->parseUrlTags($RestrictToSameDomain);

        // Insert and update the found links
        $this->insertOrUpdateLinks($this->page_links, $this->link_texts, $WhitelistPatterns);

        // Return a success boolean
        return true;
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
            return false;
        }

        // Parse the values
        $this->parseNodes($tags, $RestrictToSameDomain);
        return true;
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
            return false;
        }

        // Increase the weight added to sitemaps
        $this->link_weights = 19;

        // Parse the values
        $this->parseNodes($tags, $RestrictToSameDomain, 86400); // Re-crawl all links daily
        return true;
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
            ->filter('url');

        // Check the size
        if (sizeof($tags) === 0) {
            return false;
        }

        // Parse the values
        $tags
            ->each(function($node) use ($RestrictToSameDomain) {
                // Check for a URL
                if ($node->filter('loc')->count() < 1) {
                    return false;
                }

                // Get the url
                $url = $node
                    ->filter('loc')
                    ->first()
                    ->text();

                // Parse the URL
                $url = $this->parseFoundUrl($url, false);

                // Check for false or non-kbb domains
                if ($url === false || ($RestrictToSameDomain && parse_url($url)['host'] !== 'www.kbb.com')) {
                    return false;
                }

                // Get the re-crawl interval
                if ($node->filter('changefreq')->count() > 0) {
                    // Determine how many seconds
                    $recrawl_seconds = 1;
                    switch($node->filter('changefreq')->first()->text()) {
                        case 'yearly':
                            // Times 12 months in a year
                            $recrawl_seconds *= 12;
                        case 'monthly':
                            // Times 4 weeks in a month
                            $recrawl_seconds *= 4;
                        case 'weekly':
                            // Times 7 days in a week
                            $recrawl_seconds *= 7;
                        case 'daily':
                            // Times 24 hours in a day
                            $recrawl_seconds *= 24;
                        case 'hourly':
                        case 'always':
                            // Times 3600 seconds in an hour
                            $recrawl_seconds *= 3600;
                            break;
                        case 'never':
                        default:
                            // Never re-crawl means interval of NULL
                            $recrawl_seconds = NULL;
                            break;
                    }

                    // Set the value
                    $this->link_recrawl[$url] = $recrawl_seconds;
                }

                // Add to the text array
                $this->link_texts[$url] = '{{XML Link}}';

                // Add to the page links
                $this->page_links[] = $url;
            });
        return true;
    }

    /**
     * Process nodes to get the URLs to crawl
     * @param  Array   $nodeList             The nodes to parse
     * @param  Boolean $RestrictToSameDomain Keep on www.kbb.com or not
     */
    private function parseNodes($nodeList, $RestrictToSameDomain, $RecrawlInterval = NULL)
    {
        $nodeList
            ->each(function($node) use ($RestrictToSameDomain, $RecrawlInterval) {
                // Get the url
                $url = $node->text();

                // Check for null
                if ($url === NULL) {
                    return false;
                }

                // Parse the URL
                $url = $this->parseFoundUrl($url, false);

                // Check for false or non-kbb domains
                if ($url === false || ($RestrictToSameDomain && parse_url($url)['host'] !== 'www.kbb.com')) {
                    return false;
                }

                // Add to the text array
                $this->link_texts[$url] = '{{XML Link}}';

                // Check for a recrawl interval
                if ($RecrawlInterval !== NULL) {
                    $this->link_recrawl[$url] = $RecrawlInterval;
                }

                // Add to the page links
                $this->page_links[] = $url;
            });
    }
}
