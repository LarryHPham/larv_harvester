<?php

namespace App\Library\DataParser\KBB;

use App\Library\DataParser\ArticleParser;

class LatestNewsParser extends ArticleParser
{
    protected $body_xpath =
        [
            "//span[contains(@class, 'paragraph-two')]",
            "//div[contains(@class, 'storyCopy')]/p",
            "//div[contains(@class, 'storyCopy')]/div",
            "//div[contains(@class, 'rich-text')]"
        ];

    protected $byline_xpath       = "//div[contains(@class, 'grayTime')]";
    protected $image_xpath        = "//img[contains(@class, 'cq-dd-image')]";
    protected $image_attribute    = 'src';
    protected $stock_img_allowed = true;
//    protected $htmlBodyFallback  = "//div[contains(@class, 'storyCopy')]/p";
}
