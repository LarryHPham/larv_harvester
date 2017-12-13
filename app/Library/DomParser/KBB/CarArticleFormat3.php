<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles articles about cars
 *
 * Example pages:
 * https://www.kbb.com/car-news/all-the-latest/chevrolet-colorado-concept-fcev-to-get-real_world-army-shakedown/2000012693/
 */
class CarArticleFormat3 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ByLine;

    protected $title_xpath = '//div[contains(@class,"title-one")]//h1';
    protected $raw_article_content_xpath = '//*[contains(@class,"article-content")]//p';
    protected $image_xpath = '//*[contains(@class,"article-content")]//p/img';
    protected $category = 'automotive';
}
