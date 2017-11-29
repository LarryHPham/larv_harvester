<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\CrawlOrder;

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
    }
}
