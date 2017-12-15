<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This parser handles pages with Rich Text on them.
 *
 * Sample pages:
 * https://www.kbb.com/car-news/all-the-latest/auto-shows/2000005815/
 * https://www.kbb.com/car-news/all-the-latest/los-angeles-auto-show/13828/
 * https://www.kbb.com/car-news/all-the-latest/bmw-i8-roadster-drops-in-la/2100004887/
 * https://www.kbb.com/car-news/all-the-latest/2018-nissan-kicks-launched/2100004893/
 * https://www.kbb.com/car-news/all-the-latest/2019-kia-sorento-refreshed/2100004897/
 */
class RichTextCarousel extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ImageCarousel,
        \App\Library\DomParser\Traits\kbbArticleTypes,
        \App\Library\DomParser\Traits\ByLine,
        \App\Library\DomParser\Traits\RichText;

    protected $category = 'automotive';
}
