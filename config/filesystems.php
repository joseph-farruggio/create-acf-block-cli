<?php

return [
    'default' => 'local',
    'disks'   => [
        'local' => [
            'driver' => 'local',
            'root'   => env('DYNAMIC_DISK_PATH', storage_path('app')),
        ],
    ],
];