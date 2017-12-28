<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    /**
     * The table that the model is stored in
     * @var String
     */
    protected $table = 'keywords';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var Array
     */
    protected $guarded = [];

    /**
     * The articles that have this keyword
     * @return App\Url
     */
    public function articles()
    {
        return $this
            ->morphToMany('App\Url', 'keyword', 'article_keywords', 'keyword_id', 'article_id')
            ->withPivot('weight');
    }
}
