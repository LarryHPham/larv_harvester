<?php

namespace App\Library\DataParser;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Carbon\Carbon;

use App\Url;
use App\Library\Schema\ArticleSchema;

class ArticleParser
{
    protected $article_url;
    protected $article_hash_url;
    protected $crawl_contents;

    //Defaults to crawl page by node elements && class names
    protected $title_xpath = "//h1";
    protected $date_xpath = "//span[contains(@class, 'section-subtitle for-article time-stamp small')]";
    protected $body_xpath;
    protected $img_attribute;
    protected $image_xpath;
    protected $byline_xpath;

    protected $category = 'automotive';

    protected $verify_img_host = false;
    protected $html_body_fallback = false;


    public function __construct($url, $content)
    {
        $this->article_url = $url;
        $this->article_hash_url = Url::createHash($url);
        $this->crawl_contents = new DomCrawler($content);
    }


    public function getContentForUrl()
    {
        $title = $this->getTitle();
        $byline = $this->getByline();
        $date = (!empty($byline['date'])) ? $byline['date'] : $this->getYear($this->article_url, $title);
        if (is_null($date)) {
            $date = $this->getDateByXPath();
        }
        $paragraphs = $this->getHtmlBody();
        // $image_url = $this->getImage();

        // Generate new schema to set data points in
        $article_data = new ArticleSchema();

        // Put in known variables
        $article_data->setArticleUrl($this->article_url);

        // Run functions to set Data
        $article_data->setTitle($title);
        if (!isset($this->attribution) || !empty($byline['attribution'])) {
            $article_data->setAttribution($byline['attribution']);
        }
        $article_data->setPublisher('Kelley Blue Book');
        $article_data->setPublishedDate($this->normalizeDateFormat($date));
        $article_data->setContent($paragraphs);
        // $article_data->setImageUrl($image_url);

        // $article_data->setSource('kbb');
        // $article_data->setOriginUrl($this->url);
        // $article_data->setIsStockPhoto($this->is_stock_img);
        // $article_data->setCategory($this->category);

        return $article_data;
    }


    public function getTitle()
    {
        $title_crawler = $this->crawl_contents->filterXPath($this->title_xpath);
        if (count($title_crawler) !== 0) {
            $text = $this->cleanFormatting($title_crawler->text());
        } else {
            throw new \Exception("Required field 'title' not found for article url: '$this->url'.");
        }
        return ($text);
    }


    protected function getByline()
    {
        $byline['attribution'] = '';
        $byline['date'] = '';

        if (!is_null($this->byline_xpath)) {
            $byline_crawler = $this->crawl_contents->filterXPath($this->byline_xpath);
            if (count($byline_crawler) !== 0) {
                $byline_text = $byline_crawler->text();

                $date_markers = [
                    ' on ',
                    '- Updated Date:',
                ];

                foreach ($date_markers as $date_marker) {
                    $date_markerLength = strlen($date_marker);
                    if (strpos($byline_text, $date_marker)) {
                        $endpoint         = strpos($byline_text, $date_marker);
                        $byline['attribution'] = $this->cleanFormatting(substr($byline_text, 0, $endpoint));
                        $byline['date']   = $this->cleanFormatting(substr($byline_text, $endpoint + $date_markerLength));
                    }
                }
            }
        } else {
            $class = get_class($this);
            throw new \Exception("Class attribute `authorXPath` must be set in must be set in $class");
        }
        return $byline;
    }


    protected function getYear($url, $title)
    {
        $year_regex = '#/([0-9]{4})/#';
        $title_regex = '#([0-9]{4})#';
        if (preg_match($year_regex, $url, $matches) || preg_match($title_regex, $title, $matches)) {
            $year = str_replace('/', '', $matches[0]);
        } else {
            $year = null;
        }
        return $year;
    }


    public function getDateByXPath()
    {
        $dateCrawler = $this->crawl_contents->filterXPath($this->date_xpath);
        if (count($dateCrawler) !== 0) {
            $date = str_replace("Posted ", "", $dateCrawler->text());
        } else {
            throw new \Exception("Required field 'date' not found for article url: $this->url");
        }
        return $date;
    }


    protected function getHtmlBody()
    {
        $outer_html = '';

        if (!is_array($this->body_xpath)) {
            $this->body_xpath = [$this->body_xpath];
        }

        foreach ($this->body_xpath as $path) {
            print("PARSE PATH:".$path."\n");
            if (empty($outer_html)) {
                $content_crawler = $this->crawl_contents->filterXPath($path);
                if ($content_crawler->count() > 0) {
                    $outer_html = $content_crawler->html();
                }
            }
        }

        // These conditions are all designed to kill 'bad' paragraphs.
        // foreach ($paragraphs as $index => $paragraph) {
        //     if ($paragraph === 'Next Page'
        //         || $paragraph === ""
        //         || str_contains($paragraph, "By ")
        //         || str_contains($paragraph, "<!--")
        //         || str_contains($paragraph, "//")
        //         || (strlen($paragraph) <= 50)) {
        //         unset($paragraphs[$index]);
        //     } else {
        //         $p = $this->cleanParagraph($paragraph);
        //         $paragraphs[$index] = $p;
        //     }
        // }
        //
        // $paragraphs = array_values($paragraphs);

        return $outer_html;
    }


    protected function getImage()
    {
        $image_url = null;

        if (!is_null($this->image_xpath)) {
            $image_crawler = $this->crawl_contents->filterXPath($this->image_xpath);
            foreach ($image_crawler as $img) {
                if ($this->verify_img_host !== false) {
                    if (str_contains($img->getAttribute($this->img_attribute), $this->verify_img_host)) {
                        $image_url = ($img->getAttribute($this->img_attribute));
                    }
                } else {
                    $image_url = ($img->getAttribute($this->img_attribute));
                }
            }
            if (substr($image_url, 0, 2) === '//') {
                $length   = strlen($image_url) - 2;
                $image_url = substr($image_url, 2, $length);
            }
        } else {
            $class = get_class($this);
            throw new \Exception("Class attribute `image_xpath` must be set in must be set in $class");
        }

        return $image_url;
    }


    public function cleanFormatting($string)
    {
        $string = str_replace(["\n", "\r", "\t"], ' ', $string); // replace common html formatters with space
        $string = preg_replace('!\s+!', ' ', $string);           // replace multiple spaces with single space
        $string = trim($string);
        return $string;
    }


    protected function normalizeDateFormat($date)
    {
        if (strlen($date) == 4) {
            $date .= "/01/01 00:00:00";
        }
        $carbon = new Carbon($date);
        $transformed_date = $carbon->toDateTimeString();
        return $transformed_date;
    }


    // protected function cleanParagraph($paragraph)
    // {
    //     $paragraph = str_replace("\n", ' ', $paragraph);
    //     $paragraph = str_replace(">", '', $paragraph);
    //     $paragraph = str_replace('  ', ' ', $paragraph);
    //     $paragraph = explode(' ', $paragraph);
    //
    //     foreach ($paragraph as $index => $t) {
    //         if (ctype_alpha($t) && ($t === strtoupper($t)) && (strlen($t) > 1)) {
    //             unset($paragraph[$index]);
    //         }
    //     }
    //
    //     $paragraph = implode(' ', $paragraph);
    //     $paragraph = trim(str_replace('Read more', '', $paragraph));
    //     return $paragraph;
    // }


    protected function getStockImage()
    {
        // TODO This should be implemented differently.
        // Stock images should be retrieved from a database
        // table or the image fetch service.
        $random_integer = sprintf("%02d", rand(1, 20));
        return "http://dev-image-article.dev.sntmedia.com/01/fallback/stock/2017/03/auto_stock_$random_integer.jpg";
    }
}
