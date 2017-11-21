<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /**
     * The table that the model is stored in
     * @var String
     */
    protected $table = 'urls';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var Array
     */
    protected $guarded = [];

    /**
     * This function sets up the model so that when it is created the article
     * hash is created automatically to avoid forgetting/having to manually
     * create it
     */
    protected static function boot()
    {
        // Call the parent function
        parent::boot();

        static::creating(function($model) {
            $model->article_hash = $model->createHash($model->article_url);
        });
    }

    /**
     * This function finds models by the hash of the URL while being passed a
     * URL to make this functionality easier
     * @param  String $url The URL to search for
     * @return Url         The URL model of the matching URL (or NULL if none is
     *                     found)
     */
    public static function findByHash(String $url)
    {
        return Url::where([
            'article_hash' => Url::createHash($url)
        ])
            ->first();
    }

    /**
     * This function creates the hash of the URL
     * @param  String $url The URL to hash
     * @return String      The hashed URL
     */
    public static function createHash(String $url)
    {
        // Remove the URLs protocol
        $url = preg_replace('/^https?:\/\//', '', $url);

        // MD5 and return
        return md5($url);
    }

    /**
     * This function returns all of the URLs that link to this page
     * @return UrlArray An array of the models that this URL is linked to in
     */
    public function articleLinkedIn()
    {
        return $this
            ->belongsToMany('App\User', 'articles_linked', 'linked_article_id', 'article_id');
    }

    /**
     * This function returns all of the URLs that this URL links to
     * @return UrlArray An array of the models that this URL links to
     */
    public function articleLinksTo()
    {
        return $this
            ->belongsToMany('App\User', 'articles_linked', 'article_id', 'linked_article_id');
    }
}
