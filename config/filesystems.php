<?php

return [
    'default'  =>  env('TEMP_CACHE'),
    'disks'    => [
        'local'    => [
            'driver'  =>  'local',
            'root'    =>  storage_path('app'),
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
        ],
    ],
];
