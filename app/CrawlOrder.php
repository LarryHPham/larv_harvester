<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CrawlOrder extends Model
{
    /**
     * The table that the model is stored in
     * @var String
     */
    protected $table = 'crawl_order';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var Array
     */
    protected $guarded = [];

    /**
     * The date columns in the table
     * @var Array
     */
    protected $dates = ['claimed_at'];

    /**
     * Return the next priority CrawlOrder and marks it as taken
     * @return CrawlOrder
     */
    public static function getNextUrl()
    {
        // Create a random date
        $random_time = \Carbon\Carbon::createFromTimeStampUTC(rand(0, strtotime('2015-12-31 00:00:00')));

        // Select the URLs order descending
        $count = CrawlOrder::whereNull('claimed_at')
            ->orderBy('scheduled', 'desc')
            ->orderBy('weight', 'desc')
            ->limit(1)
            ->update([
                'claimed_at' => $random_time,
            ]);

        // Check to make sure a row was created
        if ($count < 1) {
            return NULL;
        }

        // Return the row
        return CrawlOrder::where('claimed_at', $random_time)
            ->first();
    }

    /**
     * Returns the link to the URL model
     * @return Url
     */
    public function urlModel()
    {
        return $this
            ->belongsTo('App\Url', 'article_id');
    }
}
