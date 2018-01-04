<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * Handles expert car review pages
 *
 * Example page:
 * https://www.kbb.com/lexus/ct/2017/ct-200h-expert_car_reviews/
 */
class ExpertCarReviewFormat1 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\kbbArticleTypes,
        \App\Library\DomParser\Traits\NoPublicationDate,
        \App\Library\DomParser\Traits\CmsRteKeywordText;

    protected $title_xpath = '//div[@id="Main-hero-title"]/h1';
    protected $attribution_xpath = '//p[@id="Expert-overview-byline"]';
    protected $raw_article_content_xpath = '//div[contains(@class,"mod-primary")]/div[contains(@class,"mod-primary")]';
    protected $image_xpath = '//a[contains(@class,"photos-modal")]/img';
}
