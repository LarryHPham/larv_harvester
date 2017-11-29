<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\CrawlOrder;
use App\Url;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\CrawlUrl::class,
        Commands\StartJobs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Reset the old crawls that have been abandoned due to deadlock
        $schedule
            ->call(function() {
                CrawlOrder::deleteAbandonedCrawls();
            })
            ->everyTenMinutes();

        // Add pages that have scheduled re-crawls to the order
        $schedule
            ->call(function() {
                Url::whereNotNull('recrawl_interval')
                    ->where(\DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(last_crawled) + recrawl_interval)'), '<=', \Carbon\Carbon::now())
                    ->each(function($url) {
                        // Check to see if the URL has a priority already
                        if ($url->priority !== NULL) {
                            return;
                        }

                        // Make the priority
                        $url
                            ->priority()
                            ->create([
                                'scheduled' => True,
                            ]);
                    });
            })
            ->hourly();
    }
}
