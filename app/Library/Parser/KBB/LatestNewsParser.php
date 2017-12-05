<?php

namespace App\Library\Parser\KBB;

use App\Library\Parser\ArticleParser;

class LatestNewsParser extends ArticleParser
{
    protected $body_xpath =
        [
            "//span[contains(@class, 'paragraph-two')]",
            "//div[contains(@class, 'storyCopy')]/p",
            "//div[contains(@class, 'storyCopy')]/div",
            "//div[contains(@class, 'rich-text')]/p"
        ];

    protected $byline_xpath       = "//div[contains(@class, 'grayTime')]";
    protected $image_xpath        = "//img[contains(@class, 'cq-dd-image')]";
    protected $image_attribute    = 'src';
    protected $stock_img_allowed = true;
//    protected $htmlBodyFallback  = "//div[contains(@class, 'storyCopy')]/p";
}
