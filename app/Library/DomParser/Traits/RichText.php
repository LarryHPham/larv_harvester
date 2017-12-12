<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles getting the title and article content from pages with rich
 * text divs
 */
trait RichText
{
    protected $titleXPath = '//div[contains(@class,"title-one")]//h1';
    protected $rawArticleContentXPath = '//div[contains(@class,"rich-text")]';
}
