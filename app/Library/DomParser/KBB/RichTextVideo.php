<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * This handles a type of rich text with a video
 *
 * Sample pages:
 * https://www.kbb.com/car-news/all-the-latest/2017-porsche-911-carrera-first-review-video/2100000649/
 */
class RichTextVideo extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\ByLine,
        \App\Library\DomParser\Traits\RichText,
        \App\Library\DomParser\Traits\VideoImages;

    protected $category = 'automotive';
}
