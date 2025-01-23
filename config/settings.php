<?php

use Carbon\Carbon;

return [
    'portal' => [
        'maintenance_mode' => false,
        'allow_user_registration' => false,
        'show_dropbox_files_in_dashboard' => true,
        'protected_files_string' => '_Kein',
        'feeds_default_owner_name' => 'Tides',
        'feeds_default_owner_email' => 'itunes@tides.com',
        'default_image_id' => env('DEFAULT_IMAGE_ID', 1),
        'support_email_address' => env('SUPPORT_MAIL_ADDRESS', 'support@tides.com'),
        'admin_main_address' => env('ADMIN_MAIL_ADDRESS', 'admin@tides.org'),
        'project_helpdesk_mail_address' => env('PROJECT_HELPDESK_EMAIL', 'projecthelpdesk@github.com'),
        'player_show_article_link_in_player' => false,
        'player_article_link_url' => 1,
        'player_article_link_text' => '',
        'player_enable_adaptive_streaming' => true,
        'player_transcoding_video_file_path' => '/tmp/VideoTranscoding.mp4', // Video to be played if clip has no assets
        'clip_generic_poster_image_name' => 'generic_clip_poster_image.png',
    ],
    'user' => [
        'accept_use_terms' => false,
        'language' => 'de',
        'show_subscriptions_to_home_page' => false,
    ],
    'opencast' => [
        'url' => 'localhost:8080',
        'username' => 'admin',
        'password' => 'opencast',
        'archive_path' => 'archive/mh_default_org',
        'default_workflow_id' => 'fast',
        'upload_workflow_id' => 'fast',
        'assistants_group_name' => 'ROLE_GROUP_TIDES_ASSISTANTS',
        'opencast_purge_end_date' => Carbon::now(),
        'opencast_purge_events_per_minute' => '20',
        'enable_themes_support' => true,
        'available_themes' => [
            0 => [
                'id' => '100',
                'name' => 'Default Faculty Intro-Outro TR',
                'watermarkPosition' => 'topRight',
            ],
        ],
    ],
    'streaming' => [
        'wowza' => [
            'server1' => [
                'engine_url' => 'localhost:1935/',
                'api_url' => 'localhost:8087',
                'api_username' => 'digest_user',
                'api_password' => 'digest_password',
                'content_path' => '/content/videoportal',
                'secure_token' => 'awsTides12tvv10',
                'token_prefix' => 'tides',
            ],
            'server2' => [
                'engine_url' => 'localhost:1935/',
                'api_url' => 'localhost:8087',
                'api_username' => 'digest_user',
                'api_password' => 'digest_password',
                'content_path' => '/content/videoportal',
                'secure_token' => 'awsTides12tvv10',
                'token_prefix' => 'tides',
            ],
        ],
        'nginx' => [],
        'cdn' => [
            'server1' => [
                'url' => env('CDN_SERVER_URL', 'http://localhost/'),
                'secret' => env('CDN_SERVER_SECRET', 'dsnJ23fjeq!'),
            ],

        ],
    ],
    'openSearch' => [
        'search_frontend_enable_open_search' => false,
        'url' => 'localhost',
        'port' => 9200,
        'username' => 'admin',
        'password' => 'admin',
        'prefix' => 'tides_',
    ],
    'envoy' => [
        'deploy_server' => env('DEPLOY_SERVER', 'localhost'),
        'deploy_repo' => env('DEPLOY_REPO', 'git@github.com/mmz/tides'),
        'deploy_branch' => env('DEPLOY_BRANCH', 'main'),
    ],
];
