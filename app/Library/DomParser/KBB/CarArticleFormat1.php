<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles articles about cars
 *
 * Example pages:
 * http://www.kbb.com/acura/ilx/2013-acura-ilx
 */
class CarArticleFormat1 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\PhotoSizeChanger,
        \App\Library\DomParser\Traits\NoPublicationDate;

    protected $titleXPath = '//span[@id="Expert-overview-title"]';
    protected $attributionXPath = '//p[@id="Expert-overview-byline"]/span';
    protected $rawArticleContentXPath = '//*[contains(@class,"mod-primary")]/*[contains(@class,"review-item")]';
    protected $imageXPath = '//a[contains(@class,"photos-modal")]/img';
    protected $category = 'automotive';
}
