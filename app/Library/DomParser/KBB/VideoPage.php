<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;
use GuzzleHttp\Client as GuzzleClient;

/**
 * List entry pages
 *
 * Sample pages:
 * https://www.kbb.com/car-videos/2015-jeep-renegade-suv-videos/
 */
class VideoPage extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\NoAttribution;

    protected $titleXPath = '//h1[@id="title"]';
    protected $rawArticleContentXPath = '//div[contains(@class,"videoSummary")]';
    protected $imageXPath = '//div[@id="bcplayer"]';

    /**
     * The API configuration. This is the URL to make the request to and the
     * authentication header used to make the request
     */
    private $apiUrl = 'https://edge.api.brightcove.com/playback/v1/accounts/234507581/videos/ref:';
    private $apiHeaders = [
        'Accept' => 'application/json;pk=BCpkADawqM28olhUI4W3iGH_434Ggdlrn0OnlntfmPrH6yPaLjgM0RThTyg6dAfaRYvXHQq9RaSQszcubQ39EAv8AXxNAk624eVASQxvyKNIcBZ8cLxGI63thyQ',
    ];

    /**
     * This variable is used to store the result of the API request
     */
    protected $videoInformation;

    /**
     * This class overwrites the constructor class to use an API request to fill
     * in some of the data
     * @param App\Url $url     The URL being crawled
     * @param Crawler $content The crawled content
     */
    function __construct($url, $content)
    {
        // Determine if this parser is valid
        parent::__construct($url, $content);
        if (!$this->valid) {
            return;
        }

        // Get the video ID
        $VideoId = $this
            ->content
            ->filterXPath($this->imageXPath)
            ->first()
            ->attr('data-video-id');

        // Get the video API
        $client = new GuzzleClient();
        $response = $client->request('GET', $this->apiUrl . $VideoId, [
            'headers' => $this->apiHeaders,
        ]);

        // Parse the response
        $this->videoInformation = json_decode($response->getBody(), true);
    }

    /**
     * These fields can be obtained from the API response: Title, Publication
     * Date, Article Content, and Images
     */
     
    function getTitle()
    {
        return $this->videoInformation['name'];
    }

    function getPublicationDate()
    {
        return (new \Carbon\Carbon($this->videoInformation['updated_at']))->timestamp;
    }

    function getRawArticleContent()
    {
        return $this->videoInformation['long_description'];
    }

    function getImages()
    {
        return [
            [
                'image_title' => '',
                'image_height' => null,
                'image_width' => null,
                'image_source_url' => $this->videoInformation['poster'],
            ],
        ];
    }
}
