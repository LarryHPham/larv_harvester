<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /**
     * The table that the model is stored in
     * @var string
     */
    protected $table = 'urls';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var array
     */
    protected $guarded = [];

    /**
     * The parameters to remove from the URL
     * @var array
     */
    public static $parameter_blacklist = [
        'vehicleclass',
        'intent',
        'LNX', // Used for instant cash offer
        'Lp', // Used for instant cash offer
    ];

    /**
     * This function sets up the model so that when it is created the article
     * hash is created automatically to avoid forgetting/having to manually
     * create it
     */
    protected static function boot()
    {
        // Call the parent function
        parent::boot();

        static::creating(function ($model) {
            // Check that the URL has a scheme
            if (!isset(parse_url($model->article_url)['scheme'])) {
                throw new \Exception('Protocol is Required for URL');
            }

            // Create the article hash
            $model->article_hash = $model->createHash($model->article_url);
        });
    }

    /**
     * This function finds models by the hash of the URL while being passed a
     * URL to make this functionality easier
     * @param  string $url The URL to search for
     * @return Url         The URL model of the matching URL (or null if none is
     *                     found)
     */
    public static function findByHash($url)
    {
        return Url::where([
            'article_hash' => Url::createHash($url)
        ])
            ->first();
    }

    /**
     * This function creates the hash of the URL
     * @param  string $url The URL to hash
     * @return string      The hashed URL
     */
    public static function createHash($url)
    {
        // Remove the URLs protocol
        $url = preg_replace('/^https?:\/\//', '', $url);

        // MD5 and return
        return md5($url);
    }

    /**
     * This function cleans up the URL so it can be passed to findByHash. It
     * will alphabetize the query paramters and clean up formatting
     * @param  string  $url         The URL to sanitize
     * @param  string  $ParentUrl   The URL of the page the URL was found on
     *                              (optional)
     * @param  boolean $CheckDomain Restrict to the ParentUrl domain (optional)
     * @return string               The sanitized URL
     */
    public static function sanitizeUrl($url, $ParentUrl = null, $CheckDomain = false)
    {
        // Get the parts of the url
        $url_parts = parse_url($url);

        // Check the domain (if required)
        if ($CheckDomain && $url_parts['host'] !== parse_url($ParentUrl, PHP_URL_HOST)) {
            return false;
        }

        // Build the base
        $url = $url_parts['scheme'] . '://' . $url_parts['host'];

        // Add the components
        if (isset($url_parts['path'])) {
            // Remove the trailing slash and add it
            $url .= preg_replace('/\/+$/', '', $url_parts['path']);
        }
        if (isset($url_parts['query'])) {
            // Parse the components
            parse_str($url_parts['query'], $parameters);

            // Remove the unwanted components
            foreach (self::$parameter_blacklist as $parameter) {
                unset($parameters[$parameter]);
            }

            // Determine if there are enough parameters
            if (sizeof($parameters) > 0) {
                // Sort the parameters
                ksort($parameters);

                // Add to the URL
                $url .= '?' . http_build_query($parameters);
            }
        }

        // Check for it being the parent URL
        if ($ParentUrl !== null && $url === $ParentUrl) {
            return false;
        }

        return $url;
    }

    /**
     * This function returns all of the URLs that link to this page
     * @return array An array of the models that this URL is linked to in
     */
    public function articleLinkedIn()
    {
        return $this
            ->belongsToMany('App\Url', 'articles_linked', 'linked_article_id', 'article_id');
    }

    /**
     * This function returns all of the URLs that this URL links to
     * @return array An array of the models that this URL links to
     */
    public function articleLinksTo()
    {
        return $this
            ->belongsToMany('App\Url', 'articles_linked', 'article_id', 'linked_article_id');
    }

    /**
     * The link to the crawl_order table that dictates priority
     * @return App\CrawlOrder
     */
    public function priority()
    {
        return $this
            ->hasOne('App\CrawlOrder', 'article_id');
    }

    /**
     * The non-modified keywords for the article
     * @return App\Keyword
     */
    public function keywords()
    {
        return $this
            ->morphedByMany('App\Keyword', 'keyword', 'article_keywords', 'article_id', 'keyword_id')
            ->withPivot('weight')
            ->orderBy('weight', 'DESC');
    }

    /**
     * The modified keywords for the article
     * @return App\KeywordModified
     */
    public function keywords_modified()
    {
        return $this
            ->morphedByMany('App\KeywordModified', 'keyword', 'article_keywords', 'article_id', 'keyword_id')
            ->withPivot('weight')
            ->orderBy('weight', 'DESC');
    }
}
