<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles articles about cars
 *
 * Example pages:
 * https://www.kbb.com/car-news/all-the-latest/toyota-global-hybrid-tally-now-tops-6-million/2000010234
 */
class CarArticleFormat2 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ImageCarousel,
        \App\Library\DomParser\Traits\ByLine;

    protected $titleXPath = '//div[contains(@class,"title-one")]//h1';
    protected $rawArticleContentXPath = '//*[contains(@class,"article-content")]//p';
}
