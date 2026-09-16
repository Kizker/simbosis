<?php

return [
    'default' => env('FILESYSTEM_DISK', 'public'),
    'disks' => [
        'public' => ['driver' => 'local', 'root' => public_path('storage'), 'url' => env('PUBLIC_STORAGE_URL', '/storage'), 'visibility' => 'public', 'throw' => false],
        'private' => ['driver' => 'local', 'root' => storage_path('app/private'), 'visibility' => 'private', 'throw' => false],
    ],
    'links' => [ public_path('storage') => storage_path('app/public') ],
];
