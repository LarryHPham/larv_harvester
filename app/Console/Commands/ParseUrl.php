<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use App\Url;
use App\Jobs\PageParser;
use Illuminate\Contracts\Bus\Dispatcher;

class ParseUrl extends Command
{
    protected $signature = 'parse:url {url}';

    protected $description = 'Parse a URL and grab meta data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Make sure the model exists
        $url_string = $this->argument('url');

        // Create the job
        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch(new PageParser($url_string));
    }
}
