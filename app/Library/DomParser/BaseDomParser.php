<?php

namespace App\Library\DomParser;

use App\Url;
use App\Library\Schema\ArticleSchema;

class BaseDomParser
{
    /**
     * All of the possible XPaths
     * NOTE: commented X Paths are to be left as they are not to be defined because they will break the Traits
     */
    // protected $titleXPath;
    protected $publisher_xpath = '//meta[@property="og:site_name"]';
    protected $meta_title_xpath = '//title';
    protected $meta_keywords_xpath = '//meta[@name="keywords"]';
    protected $meta_description_xpath = '//meta[@name="description"]';
    // protected $attributionXPath;
    // protected $publicationDateXPath;
    // protected $rawArticleContentXPath;
    // protected $imageXPath;

    /**
     * The XPaths that will be used to test this DOM
     * @var Array
     */
    protected $required_xpaths = [];

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
     * A Schema that represents all the datapoints to be filled in for Articles
     * @var ArticleSchema
     */
    protected $article_data;

    /**
     * The constructor function determines if the parser is valid for the given
     * DOM by checking for the title, attribution, article, and image XPaths
     * @param Url     $url     The URL model that resulted in this DOM
     * @param Crawler $content The result of the DOM Crawler on the content
     */
    public function __construct(Url $url, $content)
    {
        // Generate new schema to set data points
        $this->article_data = new ArticleSchema();

        // Add the base items to the array
        $this->required_xpaths[] = $this->title_xpath;
        $this->required_xpaths[] = $this->attribution_xpath;
        $this->required_xpaths[] = $this->raw_article_content_xpath;
        $this->required_xpaths[] = $this->image_xpath;

        // The XPaths that need to return results
        foreach ($this->required_xpaths as $xpath) {
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
        // Get the base information
        $title = $this->getTitle();
        $category = $this->category; // TODO use URL matching for this
        $meta_title = $this->getMetaTitle();
        $meta_description = $this->getMetaDescription();
        $meta_keywords = $this->getMetaKeywords();
        $attribution = $this->getAttribution();
        $publisher = $this->getPublisher();
        $publication_date = $this->getPublicationDate();

        $json_last_updated = \Carbon\Carbon::now()->timestamp; //UNIX timestamp
        $raw_article_content = $this->getTextArticleContent();
        $images = $this->getImages();

        // Use the first image as the primary image
        if (sizeof($images) > 0) {
            // Pull the first element and remove from image_array
            $primary_image = array_shift($images);
        } else {
            $primary_image = null;
        }

        // SET datapoints in article schema
        $this->article_data->setArticleId($this->url->id);
        $this->article_data->setTitle($title);
        $this->article_data->setCategory($category);
        // TODO find out the parser it used to fill this out, use URL matching for this
        // $this->article_data->setArticleType($category);

        $this->article_data->setMetaTitle($meta_title);
        $this->article_data->setMetaDescription($meta_description);
        $this->article_data->setMetaKeywords($meta_description);
        $this->article_data->setAttribution($attribution);
        $this->article_data->setPublisher($publisher);
        $this->article_data->setPublicationDate($publication_date);
        $this->article_data->setArticleUrl($this->url->article_url);
        $this->article_data->setArticleHash($this->url->article_hash);
        $this->article_data->setJsonLastUpdated($json_last_updated);
        $this->article_data->setContent($raw_article_content);
        $this->article_data->setPrimaryImage($primary_image);
        $this->article_data->setImageArray($images);


        // @TODO pass the images into the image storage service

        return $this->article_data->toJson();
    }

    /**** UTILITY FUNCTIONS ****/

    /**
     * Returns the text of all nodes that match the XPath
     * @param  String $xpath The XPath to use to get the nodes
     * @return String        The text of all of the matching nodes
     */
    protected function getTextUsingXPath($xpath)
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
     * Returns the HTML of all nodes that match the XPath
     * @param  String $xpath The XPath to use to get the nodes
     * @return String        The HTML of all of the matching nodes
     */
    protected function getHtmlUsingXPath($xpath)
    {
        $result = '';
        $nodes = $this
            ->content
            ->filterXPath($xpath)
            ->each(function ($node) use (&$result) {
                $result .= ' ' . $node->html();
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
        $string = str_replace(["\n", "\r", "\t"], ' ', $string);
        return trim(preg_replace('/\s+|&nbsp;/', ' ', $string));
    }

    /**** GET VALUE FUNCTIONS ****/

    protected function getTitle()
    {
        // Get the items
        return $this->getTextUsingXPath($this->title_xpath);
    }

    protected function getpublication_date_xpath()
    {
        // Get the items
        return $this->getTextUsingXPath($this->publication_date_xpath);
    }

    protected function getAttribution()
    {
        // Get the items
        return $this->getTextUsingXPath($this->attribution_xpath);
    }

    protected function getPublisher()
    {
        $value = $this->getMetaUsingXPath($this->publisher_xpath);
        return $value === '' || $value === null
            ? 'KBB'
            : $value;
    }

    protected function getPublicationDate()
    {
        $value = $this->getTextUsingXPath($this->publication_date_xpath);

        if ($value === '' || $value === null) {
            return null;
        }

        return (new \Carbon\Carbon($value))->timestamp;
    }

    protected function getRawArticleContent()
    {
        return $this->getHtmlUsingXPath($this->raw_article_content_xpath);
    }

    protected function getTextArticleContent()
    {
        return $this->getTextUsingXPath($this->raw_article_content_xpath);
    }

    protected function getImages()
    {
        $images = [];

        $this
            ->content
            ->filterXPath($this->image_xpath)
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

    protected function getMetaTitle()
    {
        return $this->getTextUsingXPath($this->meta_title_xpath);
    }

    protected function getMetaKeywords()
    {
        return $this->getMetaUsingXPath($this->meta_keywords_xpath);
    }

    protected function getMetaDescription()
    {
        return $this->getMetaUsingXPath($this->meta_description_xpath);
    }

    /**
     * Saves the Data of jsonFile into
     * @param  String $xpath The XPath to use to get the nodes
     * @return String        The text of all of the matching nodes
     */
    public function createJsonFile($file_path, $json_data)
    {
        $fp = fopen($file_path, 'w');
        fwrite($fp, $json_data);   //here it will print the array pretty
        fclose($fp);
    }
}
