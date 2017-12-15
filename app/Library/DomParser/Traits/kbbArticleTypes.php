<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles cases where the attribution and publication date are both
 * in a div with the class "by-line"
 */
trait kbbArticleTypes
{
    private $registered_article_types = [
        'review',
        'all-the-latest',
        'top-10',
        'car-videos'
    ];

    /**
     * This class loops over the registered article types
     * pushes into array for each type the url falls into
     * @param String $url string of the url
     * to be parsed for article types
     */
    public function getArticleType(String $url)
    {
        $article_type = [];
        foreach ($this->registered_article_types as $type) {
            if (str_contains($url, $type)) {
                array_push($article_type, $type);
            }
        }
        return $article_type;
    }
}
