<?php

namespace App\Library\Schema;

class ArticleSchema
{
    protected $title;
    protected $author;
    protected $source;
    protected $publisher;
    protected $published_date;
    protected $content;
    protected $image_url;
    protected $origin_url;
    protected $is_stock_photo;
    protected $category;
    protected $sub_category;

    public function toJson()
    {
        // TODO implement JSON schema validation
        return json_encode(get_object_vars($this));
    }

    /**
     * @return mixed
     */
    public function getIsStockPhoto()
    {
        return $this->is_stock_photo;
    }

    /**
     * @param mixed $is_stock_photo
     */
    public function setIsStockPhoto($is_stock_photo)
    {
        $this->is_stock_photo = $is_stock_photo;
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
    public function getOriginUrl()
    {
        return $this->origin_url;
    }

    /**
     * @param mixed $origin_url
     */
    public function setOriginUrl($origin_url)
    {
        $this->origin_url = $origin_url;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
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
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * @param mixed $image_url
     */
    public function setImageUrl($image_url)
    {
        $this->image_url = $image_url;
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
    protected function getFormattedImageURL($image_url)
    {
        $parsed_url = parse_url($image_url);
        if (isset($parsed_url['scheme'])) {
            return $image_url;
        } else {
            return "http://".$image_url;
        }
    }
    /**
     * This function returns keywords for the image
     * @return array
     */
    protected function getKeywords()
    {
        $keywords = array("KBB", $this->getCategory(), $this->getSubCategory());
        return $keywords;
    }
}
