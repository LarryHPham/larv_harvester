<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartJobs extends Command
{
    protected $signature = 'crawl:start_jobs {count : The number of jobs to start}';

    protected $description = 'Start any number of blank jobs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Dispatch jobs
        for ($i = 0; $i < $this->argument('count'); $i++) {
            app('Illuminate\Contracts\Bus\Dispatcher')->dispatch(new PageFetcher());
        }
    }
}
