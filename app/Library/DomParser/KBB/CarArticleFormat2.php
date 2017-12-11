<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles articles about cars
 *
 * Example pages:
 * http://www.kbb.com/acura/ilx/2013-acura-ilx
 */
class CarArticleFormat2 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ImageCarousel,
        \App\Library\DomParser\Traits\ByLine;

    protected $titleXPath = '//div[contains(@class,"title-one")]//h1';
    protected $rawArticleContentXPath = '//*[contains(@class,"article-content")]//p';
}
