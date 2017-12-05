<?php

namespace App\Library\Schema;

class ArticleSchema
{
    protected $article_id;
    protected $ready_to_publish;
    protected $title;
    protected $category;
    protected $attribution;
    protected $publisher;
    protected $article_url;
    protected $published_date;
    protected $json_last_updated;
    protected $raw_article_content;
    protected $primary_image;

    public function toJson()
    {
        // TODO implement JSON schema validation
        return json_encode(get_object_vars($this));
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->sub_category;
    }

    /**
     * @param mixed $sub_category
     */
    public function setSubCategory($sub_category)
    {
        $this->sub_category = $sub_category;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getArticleUrl()
    {
        return $this->article_url;
    }

    /**
     * @param mixed $article_url
     */
    public function setArticleUrl($article_url)
    {
        $this->article_url = $article_url;
    }

    /**
     * @return mixed
     */
    public function getAttribution()
    {
        return $this->attribution;
    }

    /**
     * @param mixed $attribution
     */
    public function setAttribution($attribution)
    {
        $this->attribution = $attribution;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @param mixed $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @return mixed
     */
    public function getPublishedDate()
    {
        return $this->published_date;
    }

    /**
     * @param mixed $published_date
     */
    public function setPublishedDate($published_date)
    {
        $this->published_date = $published_date;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->raw_article_content;
    }

    /**
     * @param mixed $raw_article_content
     */
    public function setContent($raw_article_content)
    {
        $this->raw_article_content = $raw_article_content;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->primary_image;
    }

    /**
     * @param mixed $primary_image
     */
    public function setImageUrl($primary_image)
    {
        $this->primary_image = $primary_image;
    }


    /**
     * Given image source url, hashes given URL to MD5 and returns the hash value
     * @return mixed
     */
    protected function getImageSourceId($image_source_url)
    {
        $image_source_id = hash('md5', $image_source_url);
        return $image_source_id;
    }
    /**
     * Given image url, format image URL to the required format
     * @return mixed
     */
    protected function getFormattedImageURL($primary_image)
    {
        $parsed_url = parse_url($primary_image);
        if (isset($parsed_url['scheme'])) {
            return $primary_image;
        } else {
            return "http://".$primary_image;
        }
    }
    /**
     * This function returns keywords for the image
     * @return array
     */
    protected function getKeywords()
    {
        // NOTE: Keywords come from meta tags
        $keywords = array("KBB", $this->getCategory(), $this->getSubCategory());
        return $keywords;
    }
}
