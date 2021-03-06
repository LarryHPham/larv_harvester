<?php

namespace App\Library\DomParser;

use App\Url;
use App\Library\Schema\ArticleSchema;
use App\Jobs\KeywordParser;

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
     * @var array
     */
    protected $required_xpaths = [];

    /**
     * Saved versions of the parameters passed into the constructor
     */
    protected $content;
    protected $url;

    /**
     * This boolean dictates whether the parser matches the given DOM
     * @var boolean
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

        // Save the values
        $this->url = $url;
        $this->content = $content;

        // The XPaths that need to return results
        foreach ($this->required_xpaths as $xpath) {
            if (sizeof($content->filterXPath($xpath)) === 0) {
                return false;
            }
        }

        // grab base data
        $title = $this->getTitle();
        $raw_article_content = $this->getRawArticleContent();


        // checks if any data points that come back from xpaths are empty
        if (empty($raw_article_content) || empty($title)) {
            return false;
        }

        // Use the first image as the primary image
        // set the base data to the schema
        $this->article_data->setContent($raw_article_content);
        $this->article_data->setTitle($title);


        // Save the validity
        $this->valid = true;
    }

    /**
     * This function returns the JSON file to be saved for the article
     * @return array The JSON to be used
     */
    public function getValues()
    {
        // Get the base information
        $category = $this->category;
        $url_model = $this->url;

        $article_type = $this->getArticleType($url_model->article_url);
        if (empty($article_type)) {
            $article_type = $this->getArticleType($this->article_data->getTitle());
        }
        $meta_title = $this->getMetaTitle();
        $meta_description = $this->getMetaDescription();
        $meta_keywords = $this->getMetaKeywords();
        $attribution = $this->getAttribution();
        $publisher = $this->getPublisher();
        $publication_date = $this->getPublicationDate();
        $json_last_updated = \Carbon\Carbon::now()->timestamp; //UNIX timestamp
        $images = $this->getImages();

        // Dispatch the job to parse the article text
        dispatch((new KeywordParser($this->url, $this->getArticleKeywordContent()))->onQueue(env('PARSE_QUEUE')));

        // TODO: need to know what happens if primary image is null
        if (sizeof($images) == 0) {
            $primary_image = null;
        } else {
            $primary_image = array_shift($images);
        }
        $this->article_data->setPrimaryImage($primary_image);
        $this->article_data->setImageArray($images);


        // SET datapoints in article schema
        $this->article_data->setCategory($category);
        $this->article_data->setArticleType($article_type);
        $this->article_data->setMetaTitle($meta_title);
        $this->article_data->setMetaDescription($meta_description);
        $this->article_data->setMetaKeywords($meta_keywords);
        $this->article_data->setAttribution($attribution);
        $this->article_data->setPublisher($publisher);
        $this->article_data->setPublicationDate($publication_date);
        $this->article_data->setArticleUrl($url_model->article_url);
        $this->article_data->setArticleHash($url_model->article_hash);
        $this->article_data->setJsonLastUpdated($json_last_updated);

        // @TODO pass the images into the image storage service

        return $this->article_data->toJson();
    }

    /**** UTILITY FUNCTIONS ****/

    /**
     * Returns the text of all nodes that match the XPath
     * @param  string $xpath The XPath to use to get the nodes
     * @return string        The text of all of the matching nodes
     */
    protected function getTextUsingXPath($xpath, $joiner = ' ')
    {
        $result = '';
        $nodes = $this
            ->content
            ->filterXPath($xpath)
            ->each(function ($node) use (&$result, $joiner) {
                $result .= $joiner . $this->cleanStringFormatting($node->text());
            });

        return trim(preg_replace('/[^\S\n]+/', ' ', $result));
    }

    /**
     * Returns the HTML of all nodes that match the XPath
     * @param  string $xpath The XPath to use to get the nodes
     * @return string        The HTML of all of the matching nodes
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
     * @param  string $xpath The XPath that matches the desired meta tag
     * @return string        The content attribute of the meta tag
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
     * @param  string  $string        The string to format
     * @return string                 The clean string
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

    protected function getArticleType(String $url)
    {
        // return array of article types based on known types in url
        return [];
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

    protected function getArticleKeywordContent()
    {
        // Get the XPath to use
        if (isset($this->keyword_article_content_xpath)) {
            $xpath = $this->keyword_article_content_xpath;
        } else {
            $xpath = $this->raw_article_content_xpath;
        }

        // Return with double enter seperation
        return preg_replace('/\s\s+/', "\n", $this->getTextUsingXPath($xpath, "\n"));
    }
}
