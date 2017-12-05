<?php

namespace App\Library\DataParser\KBB;

use App\Library\DataParser\ArticleParser;

class Top10ListParser extends ArticleParser
{
    // TODO May want to add complex functionality to add list-element detail pages to (Jonathan)
    // (It's currently only designed to parse the landing-pages for each list)

    // TODO Find a way to restrict parsing to the landing page (until above TODO is achieved)
    protected $body_xpath      = "//div[contains(@class, 'article-content')]/p";
    protected $byline_xpath    = "//p[contains(@id, 'Expert-overview-byline')]";
    protected $image_xpath     = "//meta[contains(@property, 'og:image')]";
    protected $image_attribute = "content";
    protected $attribution     = "KBB.com Editors";
}
