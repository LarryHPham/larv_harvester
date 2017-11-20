<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use App\Url;
use App\Jobs\CrawlUrl as CrawlUrlJob;
use Illuminate\Contracts\Bus\Dispatcher;

class CrawlUrl extends Command
{
    protected $signature = 'crawl:url {url}';

    protected $description = 'Crawl a URL and record the URLs seen on the page';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Make sure the model exists
        $url_string = $this->argument('url');
        $url = Url::where('article_url', $url_string)
            ->first();

        // If no URL exists, create one
        if ($url === NULL) {
            $url = new Url([
                'article_url' => $url_string,
            ]);
            $url->save();
        }

        // Create the job
        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch(new CrawlUrlJob($url));
    }
}
