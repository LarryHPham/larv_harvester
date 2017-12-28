<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeywordModified extends Model
{
    /**
     * The table that the model is stored in
     * @var String
     */
    protected $table = 'keywords_modified';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var Array
     */
    protected $guarded = [];

    /**
     * Don't try to fill in the timestamp fields (they don't exist)
     * @var Boolean
     */
    public $timestamps = false;

    /**
     * The articles that have this keyword pair
     * @return App\Url
     */
    public function articles()
    {
        return $this
            ->morphToMany('App\Url', 'keyword', 'article_keywords', 'keyword_id', 'article_id')
            ->withPivot('weight');
    }

    /**
     * The base keyword for this pair
     * @return App\Keyword
     */
    public function keyword()
    {
        return $this
            ->belongsTo('App\Keyword');
    }

    /**
     * The modifying keyword
     * @return App\Keyword
     */
    public function modifier()
    {
        return $this
            ->belongsTo('App\Keyword', 'modifier_id');
    }
}
