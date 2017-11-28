<?php

use Illuminate\Database\Seeder;
use App\Url;
use App\Jobs\PageFetcher;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('UrlTableSeeder');
    }
}

class UrlTableSeeder extends Seeder
{
    public function run()
    {
        $urls = [
            'https://www.kbb.com/sitemap.xml',
            'https://www.kbb.com',
            'http://rss.kbb.com/kbb-car-reviews?format=xml',
            'http://rss.kbb.com/kbb-car-news?format=xml',
            'http://rss.kbb.com/kbb-car-videos?format=xml',
        ];

        foreach ($urls as $url) {
            // Make the model
            $model = Url::firstOrCreate([
                'article_url' => $url,
            ]);

            // Add to the job queue
            $model
                ->priority()
                ->create([
                    'scheduled' => True,
                    'claimed_at' => NULL,
                ]);

            // Dispatch the job
            dispatch(new PageFetcher());
        }
    }
}
