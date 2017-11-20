<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'urls';

    protected $guarded = [];

    protected static function boot()
    {
        // Call the parent function
        parent::boot();

        static::creating(function($model) {
            $model->article_hash = $model->createHash($model->article_url);
        });
    }

    public static function findByHash(String $url)
    {
        return Url::where([
            'article_hash' => Url::createHash($url)
        ])
            ->first();
    }

    public static function createHash(String $url)
    {
        return md5($url);
    }

    public function articleLinkedIn()
    {
        return $this
            ->belongsToMany('App\User', 'articles_linked', 'linked_article_id', 'article_id');
    }

    public function articleLinksTo()
    {
        return $this
            ->belongsToMany('App\User', 'articles_linked', 'article_id', 'linked_article_id');
    }
}
