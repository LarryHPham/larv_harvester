<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CrawlOrder extends Model
{
    /**
     * The table that the model is stored in
     * @var string
     */
    protected $table = 'crawl_order';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var array
     */
    protected $guarded = [];

    /**
     * The date columns in the table
     * @var array
     */
    protected $dates = ['claimed_at'];

    /**
     * Return the next priority CrawlOrder and marks it as taken
     * @return CrawlOrder
     */
    public static function getNextUrl()
    {
        // Create a random date
        $random_time = Carbon::createFromTimeStampUTC(rand(0, strtotime('2017-01-01 00:00:00')));

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
            return null;
        }

        // Return the row
        return CrawlOrder::where('claimed_at', $random_time)
            ->first();
    }

    /**
     * This function resets abandoned crawls (crawls that have a claimed time
     * older than 10 minutes old)
     */
    public static function resetAbandonedCrawls()
    {
        // Reset anything between 2017-06-01 and 10 minutes ago
        CrawlOrder::whereBetween('claimed_at', [
            new Carbon('2017-02-01 00:00:00'),
            Carbon::now()->subMinutes(10),
        ])
            ->update([
                'claimed_at' => null,
            ]);
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
