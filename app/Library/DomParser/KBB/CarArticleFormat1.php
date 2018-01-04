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
        \App\Library\DomParser\Traits\kbbArticleTypes,
        \App\Library\DomParser\Traits\NoPublicationDate,
        \App\Library\DomParser\Traits\CmsRteKeywordText;

    protected $title_xpath = '//span[@id="Expert-overview-title"]';
    protected $attribution_xpath = '//p[@id="Expert-overview-byline"]/span';
    protected $raw_article_content_xpath = '//*[contains(@class,"mod-primary")]/*[contains(@class,"review-item")]';
    protected $image_xpath = '//a[contains(@class,"photos-modal")]/img';
}
