<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
        'videos' => [
            'driver' => 'local',
            'root' => env('NFS_PATH', storage_path('app/videos')),
            'url' => env('APP_URL').'/videos',
        ],

        'streamable_videos' => [
            'driver' => 'local',
            'root' => storage_path('app/streamable_videos'),
            'url' => env('APP_URL').'/streamable_videos',
            'visibility' => 'public',
        ],

        'video_dropzone' => [
            'driver' => 'local',
            'root' => env('VIDEOS_DROPZONE', storage_path('app/video_drop_zone')),
        ],

        'opencast_archive' => [
            'driver' => 'local',
            'root' => env('OPENCAST_ARCHIVE_PATH', storage_path('app/opencast')),
        ],

        'documents' => [
            'driver' => 'local',
            'root' => env('DOCUMENTS_PATH', storage_path('app/documents')),
            'url' => env('APP_URL').'/documents',
        ],
        'assetsSymLinks' => [
            'driver' => 'local',
            'root' => env('ASSETS_SYMBOLIC_LINKS_PATH', storage_path('app/assetsSymLinks')),
            'url' => env('APP_URL').'/assetsSymLinks',
            'visibility' => 'public',
        ],
        'thumbnails' => [
            'driver' => 'local',
            'root' => env('PLAYER_THUMBNAILS_PATH', storage_path('app/thumbnails')),
            'url' => env('APP_URL').'/thumbnails',
            'visibility' => 'public',
        ],
        'images' => [
            'driver' => 'local',
            'root' => env('IMAGES_PATH', storage_path('app/images')),
            'url' => env('APP_URL').'/images',
            'visibility' => 'public',
        ],
        'podcasts' => [
            'driver' => 'local',
            'root' => env('IMAGES_PATH', storage_path('app/podcasts-files')),
            'url' => env('APP_URL').'/podcasts-files',
            'visibility' => 'public',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('streamable_videos') => storage_path('app/streamable_videos'),
        public_path('thumbnails') => storage_path('app/thumbnails'),
        public_path('links') => storage_path('app/assetsSymLinks'),
        public_path('images') => storage_path('app/images'),
        public_path('podcasts-files') => storage_path('app/podcasts-files'),
    ],

];
