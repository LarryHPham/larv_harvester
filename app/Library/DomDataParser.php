<?php

namespace App\Library;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

// internal imports
use App\Url;
use App\Library\Parser\KBB\ExpertReviewParser;
use App\Library\Parser\KBB\CarVideosParser;
use App\Library\Parser\KBB\LatestNewsParser;
use App\Library\Parser\KBB\Top10ListParser;

class DomDataParser
{
    protected $article_data;

    public function __construct($url, $content)
    {
        // TODO Set Base Interface that is the requirements for a basic article
        $url = str_replace("NULL", "", $url);// TODO temp
        print("UrlParser Constructing: ".$url."\n");
        $parser = $this->getCorrectParserForUrlPattern($url, $content);
        $this->article_data = $parser->getContentForUrl();
        var_dump($this->article_data);
    }

    public function handle()
    {
    }

    public function getCorrectParserForUrlPattern($url, $content)
    {
        switch (true) {
          case str_contains($url, 'expert_car_reviews'):
              print("Expert Review Parser: ".$url."\n");
              $parser = new ExpertReviewParser($url, $content);
          break;
          case str_contains($url, 'car-news/all-the-latest'):
              print("Latest News Parser: ".$url."\n");
              $parser = new LatestNewsParser($url, $content);
          break;
          case str_contains($url, 'car-videos'):
              print("Car Videos Parser: ".$url."\n");
              $parser = new CarVideosParser($url, $content);
          break;
          case str_contains($url, 'car-reviews-and-news/top-10'):
              print("Top 10 Parser: ".$url."\n");
              $parser = new Top10ListParser($url, $content);
          break;
          default:
              throw new \Exception("No acceptable url patterns found in url: $url");
        }

        return $parser;
    }
}
