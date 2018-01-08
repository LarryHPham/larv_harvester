<?php

namespace App\Library\DomParser\Traits;

/**
 * This trait handles cases where the attribution and publication date are both
 * in a div with the class "by-line"
 */
trait kbbArticleTypes
{
    /**
     * Known Kbb Article types that will be parsed
     * and added into article type as an array of each item
     * @var array
     */
    private $kbb_article_types = [
        'review',
        'latest',
        'top-10',
        'video'
    ];

    /**
     * Known Kbb Category that will be returned
     * @var string
     */
    protected $category = 'automotive';

    /**
     * This class loops over the registered article types
     * pushes into array for each type the url falls into
     * @param string $string string of the url
     * to be parsed for article types
     */
    public function getArticleType(String $string)
    {
        $article_type = [];
        foreach ($this->kbb_article_types as $type) {
            if (str_contains(strtolower($string), $type)) {
                array_push($article_type, $type);
            }
        }
        return $article_type;
    }
}
