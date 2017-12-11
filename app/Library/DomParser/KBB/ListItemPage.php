<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * A specific list items
 *
 * Sample pages:
 * https://www.kbb.com/car-reviews-and-news/top-10/best-luxury-cars-under-35000/2100001730-10/
 */
class ListPage extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\Posted,
        \App\Library\DomParser\Traits\NoAttribution;

    protected $titleXPath = '//h1[contains(@class,"title")]|//a[@id="Vehicle-title"]';
    protected $rawArticleContentXPath = '//div[contains(@class,"article-content ")]/p';
    protected $imageXPath = '//img[contains(@class,"editorial")]';
}
