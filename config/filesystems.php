<?php

return [
    'default'  =>  'localtemp',
    'disks'    => [
        'localtemp'    => [
            'driver'  =>  'local',
            'root'    =>  storage_path('app'),
        ],
        'localarticle'    => [
            'driver'  =>  'local',
            'root'    =>  storage_path('app/web-harvester'),
        ],
        //AWS s3 connections below
        'temp' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_TEMP_CACHE_BUCKET'),
        ],
        'article' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_ARTICLE_JSON_BUCKET'),
            'root' => 'web-harvester',
        ],
    ],
];
