<?php

namespace App\Library\DataParser\KBB;

use App\Library\DataParser\ArticleParser;

class CarVideosParser extends ArticleParser
{
    protected $body_xpath      = "//div[contains(@class, 'videoSummary blackLabel')]/span/span";
    protected $byline_xpath    = "//p[contains(@id, 'Expert-overview-byline')]";
    protected $image_xpath     = "//div[contains(@class, 'bc-video-player')]";
    protected $image_attribute = 'data-video-id';
    protected $attribution     = "KBB.com Editors";
    // This function is unique in that its grabs KBB's article id to construct the image-url.
    // This is necessary because the video's image thumbnail doesn't appear in the html until
    // after initial page load.
    protected function getImage()
    {
        $image_id = parent::getImage();
        $image_url = "file.kbb.com/kbb/images/videos/{$image_id}hp.jpg";
        return $image_url;
    }
}
