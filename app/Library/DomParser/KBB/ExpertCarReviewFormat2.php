<?php

namespace App\Library\DomParser\KBB;

use App\Library\DomParser\BaseDomParser;

/**
 * Handles expert car review pages
 *
 * Example page:
 * https://www.kbb.com/volkswagen/atlas/2018/launch-edition-expert_car_reviews/?vehicleid=424751
 */
class ExpertCarReviewFormat2 extends BaseDomParser
{
    use
        \App\Library\DomParser\Traits\kbbArticleTypes,
        \App\Library\DomParser\Traits\NoPublicationDate,
        \App\Library\DomParser\Traits\CmsRteKeywordText;

    protected $title_xpath = '//div[@id="Main-hero-title"]/h1';
    protected $attribution_xpath = '//p[@id="Expert-overview-byline"]';
    protected $raw_article_content_xpath = '//div[contains(@class,"mod-primary")]';
    protected $image_xpath = '//div[contains(@class,"vehicle-gallery")]//img';
}
