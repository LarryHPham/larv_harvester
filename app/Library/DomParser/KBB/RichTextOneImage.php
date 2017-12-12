<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This handles a type of rich text with only one image
 *
 * Sample pages:
 * https://www.kbb.com/car-news/all-the-latest/2018-kia-niro-plug-in-hybrid-announced/2100004896/
 */
class RichTextOneImage extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ByLine,
        \App\Library\DomParser\Traits\RichText;

    protected $imageXPath = '//img[contains(@class,"main-content-image")]';
    protected $category = 'automotive';
}
