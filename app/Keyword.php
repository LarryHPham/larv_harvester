<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    /**
     * The table that the model is stored in
     * @var string
     */
    protected $table = 'keywords';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var array
     */
    protected $guarded = [];

    /**
     * Don't try to fill in the timestamp fields (they don't exist)
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The articles that have this keyword
     * @return App\Url
     */
    public function articles()
    {
        return $this
            ->morphToMany('App\Url', 'keyword', 'article_keywords', 'keyword_id', 'article_id')
            ->withPivot('weight')
            ->orderBy('weight', 'DESC');
    }
}
