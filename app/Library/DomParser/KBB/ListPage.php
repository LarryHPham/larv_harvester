<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * List entry pages
 *
 * Sample pages:
 * https://www.kbb.com/car-reviews-and-news/top-10/best-luxury-cars-under-35000/2100001730/
 * https://www.kbb.com/car-reviews-and-news/top-10/best-back-to-school-cars-2017/2100004482
 * https://www.kbb.com/car-reviews-and-news/top-10/most-fuel-efficient-suvs/2100004366-6/
 */
class ListPage extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\Posted,
        \App\Library\DomParser\Traits\kbbArticleTypes,
        \App\Library\DomParser\Traits\NoAttribution;

    protected $title_xpath = '//h1[contains(@class,"title")]|//a[@id="Vehicle-title"]';
    protected $raw_article_content_xpath = '//div[contains(@class,"article-content ")]/p';
    protected $image_xpath = '//div[contains(@class,"vehicle-image-container")]/img';
    protected $category = 'automotive';
}
