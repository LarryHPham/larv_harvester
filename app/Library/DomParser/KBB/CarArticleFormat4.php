<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles articles about cars
 *
 * Example pages:
 * https://www.kbb.com/car-news/all-the-latest/2014-compact_sedan-comparison-test-packing-more-for-less/2000010986/
 */
class CarArticleFormat4 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ByLine,
        \App\Library\DomParser\Traits\VideoImages;

    protected $titleXPath = '//div[contains(@class,"title-one")]//h1';
    protected $rawArticleContentXPath = '//*[contains(@class,"article-content")]//p';
}
