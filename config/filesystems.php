<?php

return [
    'default'  =>  'local',
    'disks'    => [
        'local'    => [
            'driver'  =>  'local',
            'root'    =>  storage_path('app'),
        ],
        'public'   => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'visibility' => 'public',
        ],
        //AWS s3 connection below
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
    ],
];
