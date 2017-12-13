<?php

namespace App\Library\Schema;

class ArticleSchema
{
    protected $article_id;
    protected $ready_to_publish = false;
    protected $title;
    protected $category;
    protected $article_type;
    protected $meta_title;
    protected $meta_description;
    protected $meta_keywords;
    protected $attribution;
    protected $publisher;
    protected $publication_date;
    protected $article_url;
    protected $article_hash;
    protected $json_last_updated;
    protected $raw_article_content;
    protected $primary_image;
    protected $image_array;

    public function toJson()
    {
        // TODO implement JSON schema validation
        return json_encode(get_object_vars($this), JSON_UNESCAPED_SLASHES);
    }

    public function setArticleId($article_id)
    {
        $this->article_id = $article_id;
    }

    public function setReadyToPublish($ready_to_publish)
    {
        $this->ready_to_publish = $ready_to_publish;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function setArticleType($article_type)
    {
        $this->article_type = $article_type;
    }

    public function setMetaTitle($meta_title)
    {
        $this->meta_title = $meta_title;
    }

    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;
    }

    public function setMetaKeywords($meta_keywords)
    {
        $this->meta_keywords = $meta_keywords;
    }

    public function setAttribution($attribution)
    {
        $this->attribution = $attribution;
    }

    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    public function setPublicationDate($publication_date)
    {
        $this->publication_date = $publication_date;
    }

    public function setArticleUrl($article_url)
    {
        $this->article_url = $article_url;
    }

    public function setArticleHash($article_hash)
    {
        $this->article_hash = $article_hash;
    }

    public function setJsonLastUpdated($json_last_updated)
    {
        $this->json_last_updated = $json_last_updated;
    }

    public function setContent($raw_article_content)
    {
        $this->raw_article_content = $raw_article_content;
    }

    public function setPrimaryImage($primary_image)
    {
        $this->primary_image = $primary_image;
    }

    public function setImageArray($image_array)
    {
        $this->image_array = $image_array;
    }
}
