<?php

namespace App\Library\Parser\KBB;

use App\Library\Parser\ArticleParser;

class ExpertReviewParser extends ArticleParser
{
    protected $body_xpath       = "//div[contains(@class, 'cms-rte')]";
    protected $byline_xpath     = "//p[contains(@id, 'Expert-overview-byline')]";
    protected $image_xpath      = "//img[boolean(@alt)]";
    protected $image_attribute  = 'src';
    protected $sub_category     = 'expert-reviews';
    protected $verify_img_host = 'file.kbb.com';
}
