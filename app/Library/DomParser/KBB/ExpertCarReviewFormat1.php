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
    use \App\Library\DomParser\Traits\NoPublicationDate;

    protected $titleXPath = '//div[@id="Main-hero-title"]/h1';
    protected $attributionXPath = '//p[@id="Expert-overview-byline"]';
    protected $rawArticleContentXPath = '//div[contains(@class,"mod-primary")]/div[contains(@class,"mod-primary")]';
    protected $imageXPath = '//a[contains(@class,"photos-modal")]/img';
}
