<?php

namespace App\Library\DomParser;

use App\Url;

class BaseDomParser
{
    /**
     * All of the possible XPaths
     */
    // protected $titleXPath;
    protected $metaTitleXPath = '//title';
    protected $metaKeywordsXPath = '//meta[@name="keywords"]';
    protected $metaDescriptionXPath = '//meta[@name="description"]';
    // protected $attributionXPath;
    protected $publisherXPath = '//meta[@property="og:site_name"]';
    // protected $publicationDateXPath;
    // protected $rawArticleContentXPath;
    // protected $imageXPath;

    /**
     * The XPaths that will be used to test this DOM
     * @var Array
     */
    protected $requiredXPaths = [];

    /**
     * Saved versions of the parameters passed into the constructor
     */
    protected $content;
    protected $url;

    /**
     * This boolean dictates whether the parser matches the given DOM
     * @var Boolean
     */
    public $valid = false;

    /**
     * The constructor function determines if the parser is valid for the given
     * DOM by checking for the title, attribution, article, and image XPaths
     * @param Url     $url     The URL model that resulted in this DOM
     * @param Crawler $content The result of the DOM Crawler on the content
     */
    public function __construct(Url $url, $content)
    {
        // Add the base items to the array
        $this->requiredXPaths[] = $this->titleXPath;
        $this->requiredXPaths[] = $this->attributionXPath;
        $this->requiredXPaths[] = $this->rawArticleContentXPath;
        $this->requiredXPaths[] = $this->imageXPath;

        // The XPaths that need to return results
        foreach ($this->requiredXPaths as $xpath) {
            if (sizeof($content->filterXPath($xpath)) === 0) {
                return false;
            }
        }

        // Save the validity
        $this->valid = true;

        // Save the values
        $this->url = $url;
        $this->content = $content;
    }

    /**
     * This function returns the JSON file to be saved for the article
     * @return Array The JSON to be used
     */
    public function getValues()
    {
        // @TODO add a switch for rendering via PhantomJS

        // Get the base information
        $article_data = [
            'article_id' => $this->url->id,
            'ready_to_publish' => false,
            'title' => $this->getTitle(),
            'category' => null, // @TODO fill out this field
            'article_type' => null, // @TODO fill out this field
            'meta_title' => $this->getMetaTitle(),
            'meta_keywords' => $this->getMetaKeywords(),
            'meta_description' => $this->getMetaDescription(),
            'attribution' => $this->getAttribution(),
            'publisher' => $this->getPublisher(),
            'article_url' => $this->url->article_url,
            'publication_date' => $this->getPublicationDate(),
            'json_last_updated' => \Carbon\Carbon::now()->timestamp,
            'raw_article_content' => $this->getRawArticleContent(),
            'primary_image' => [],
            'image_array' => $this->getImages(),
        ];

        // Use the first image as the primary image
        if (sizeof($article_data['image_array']) > 0) {
            // Pull the first element and remove from image_array
            $article_data['primary_image'] = array_shift($article_data['image_array']);
        }

        // @TODO pass the images into the image storage service

        return $article_data;
    }

    /**** UTILITY FUNCTIONS ****/

    /**
     * Returns the text of all nodes that match the XPath
     * @param  String $xpath The XPath to use to get the nodes
     * @return String        The text of all of the matching nodes
     */
    protected function getUsingXPath($xpath)
    {
        $result = '';
        $nodes = $this
            ->content
            ->filterXPath($xpath)
            ->each(function ($node) use (&$result) {
                $result .= ' ' . $node->text();
            });

        return $this->cleanStringFormatting($result);
    }

    /**
     * Returns the content attribute of the meta tag
     * @param  String $xpath The XPath that matches the desired meta tag
     * @return String        The content attribute of the meta tag
     */
    protected function getMetaUsingXPath($xpath)
    {
        // Get the nodes
        $nodes = $this
            ->content
            ->filterXPath($xpath);

        if (sizeof($nodes) === 0) {
            return '';
        }

        return $this->cleanStringFormatting($nodes
            ->first()
            ->attr('content'));
    }

    /**
     * This function cleans up formatting of a string
     * @param  String  $string        The string to format
     * @return String                 The clean string
     */
    protected function cleanStringFormatting($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**** GET VALUE FUNCTIONS ****/

    protected function getTitle()
    {
        // Get the items
        return $this->getUsingXPath($this->titleXPath);
    }

    protected function getMetaTitle()
    {
        return $this->getUsingXPath($this->metaTitleXPath);
    }

    protected function getMetaKeywords()
    {
        return $this->getMetaUsingXPath($this->metaKeywordsXPath);
    }

    protected function getMetaDescription()
    {
        return $this->getMetaUsingXPath($this->metaDescriptionXPath);
    }

    protected function getAttribution()
    {
        // Get the items
        return $this->getUsingXPath($this->attributionXPath);
    }

    protected function getPublisher()
    {
        $value = $this->getMetaUsingXPath($this->publisherXPath);
        return $value === '' || $value === null
            ? 'KBB'
            : $value;
    }

    protected function getPublicationDate()
    {
        $value = $this->getUsingXPath($this->publicationDateXPath);

        if ($value === '' || $value === null) {
            return null;
        }

        return (new \Carbon\Carbon($value))->timestamp;
    }

    protected function getRawArticleContent()
    {
        return $this->getUsingXPath($this->rawArticleContentXPath);
    }

    protected function getImages()
    {
        $images = [];

        $this
            ->content
            ->filterXPath($this->imageXPath)
            ->each(function ($image) use (&$images) {
                // Get the image
                $image_data = [
                    'image_title' => $image->attr('alt'),
                    'image_height' => null,
                    'image_width' => null,
                    'image_source_url' => $image->attr('src'),
                ];

                if (isset($this->changePhotoSize)) {
                    $image_data = $this->changePhotoSizeFromObject($image_data);
                }

                $unique = true;
                foreach ($images as $image) {
                    if ($image['image_source_url'] === $image_data['image_source_url']) {
                        $unique = false;
                    }
                }

                if ($unique) {
                    $images[] = $image_data;
                }
            });

        return $images;
    }
}
