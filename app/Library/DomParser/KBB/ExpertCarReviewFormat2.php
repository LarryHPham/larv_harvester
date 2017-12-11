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
    use \App\Library\DomParser\Traits\NoPublicationDate;

    protected $titleXPath = '//div[@id="Main-hero-title"]/h1';
    protected $attributionXPath = '//p[@id="Expert-overview-byline"]';
    protected $rawArticleContentXPath = '//div[contains(@class,"mod-primary")]';
    protected $imageXPath = '//div[contains(@class,"vehicle-gallery")]//img';
}
