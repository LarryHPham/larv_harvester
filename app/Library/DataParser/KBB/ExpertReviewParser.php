<?php

namespace App\Library\DataParser\KBB;

use App\Library\DataParser\ArticleParser;

class ExpertReviewParser extends ArticleParser
{
    protected $body_xpath       = "//div[contains(@class, 'cms-rte')]";
    protected $byline_xpath     = "//p[contains(@id, 'Expert-overview-byline')]";
    protected $image_xpath      = "//img[boolean(@alt)]";
    protected $image_attribute  = 'src';
    protected $verify_img_host = 'file.kbb.com';
}
