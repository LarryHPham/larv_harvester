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
        $url = Url::findByHash($url_string);
        // If no URL exists, create one
        if ($url === null) {
            // Make the URL
            $url = new Url([
                'article_url' => $url_string,
            ]);
            $url->save();

            // Create a priority entry
            $url
                ->priority()
                ->create([]);
        }

        // If no priority exists, create one
        if ($url->priority === null) {
            $url
                ->priority()
                ->create([]);
        }

        // Make the crawl_order
        $url
            ->priority
            ->save([
                'scheduled' => true,
                'weight' => 20,
            ]);

        //run Page Parser
        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch(new PageParser($url_string));
    }
}
