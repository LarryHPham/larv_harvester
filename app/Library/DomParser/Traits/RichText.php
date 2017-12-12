<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the title and article content from pages with rich
 * text divs
 */
trait RichText
{
    protected $title_xpath = '//div[contains(@class,"title-one")]//h1';
    protected $raw_article_content_xpath = '//div[contains(@class,"rich-text")]';
}
