<?php

declare(strict_types=1);

/**
 * Copy to config.php and fill in production values.
 * Keep this file outside the web root.
 */
return [
    'app' => [
        'name' => 'Northstar Load',
        'brand' => 'Northstar Load',
        'url' => 'http://localhost:8080',
        'env' => 'production', // local|production
        'debug' => false,
        'timezone' => 'UTC',
    ],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'northstar_load',
        'user' => 'northstar',
        'pass' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],

    'session' => [
        'name' => 'NSLOADSESSID',
        'lifetime' => 7200,
        // auto = Secure cookie only when the request is HTTPS (works behind Cloudflare)
        'secure' => 'auto',
        'httponly' => true,
        'samesite' => 'Lax',
        'domain' => '', // leave blank; set only if you share cookies across subdomains
    ],

    'security' => [
        'login_max_attempts' => 8,
        'login_window_seconds' => 900,
        'csrf_key' => 'csrf_token',
    ],

    'media' => [
        'max_image_bytes' => 5 * 1024 * 1024,
        'max_audio_bytes' => 15 * 1024 * 1024,
        'max_video_bytes' => 40 * 1024 * 1024,
        'max_image_dimension' => 4096,
        'allowed_extensions' => ['png', 'jpg', 'jpeg', 'webp', 'mp3', 'ogg', 'mp4'],
    ],

    'builds' => [
        'expire_days' => 30,
        'runtime_version' => '1.2.0',
    ],

    // Hosted FiveM loadscreens (players load from this origin)
    'hosting' => [
        'load_base_url' => 'https://load.northstarscripts.us',
    ],

    // Paid plan selection — leave disabled until Stripe/PayPal is wired.
    'billing' => [
        'enabled' => false,
        'selectable_plans' => ['free'],
    ],

    'entitlements' => [
        // Philosophy: Free can ship a complete loading screen.
        // Paid plans add capacity + convenience, not a paywall on basics.
        'free' => [
            'max_projects' => 5,
            'max_media' => 60,
            'max_builds_per_day' => 15,
            'max_components' => 24,
            'features' => [
                'youtube_music' => true,
                'music_file' => true,
                'slideshow_background' => true,
                'video_background' => false,
                'staff' => true,
                'announcements' => true,
                'ken_burns' => false,
            ],
        ],
        'standard' => [
            'max_projects' => 25,
            'max_media' => 250,
            'max_builds_per_day' => 50,
            'max_components' => 50,
            'features' => [
                'youtube_music' => true,
                'music_file' => true,
                'slideshow_background' => true,
                'video_background' => false,
                'staff' => true,
                'announcements' => true,
                'ken_burns' => true,
            ],
        ],
        'pro' => [
            'max_projects' => 200,
            'max_media' => 2000,
            'max_builds_per_day' => 200,
            'max_components' => 80,
            'features' => [
                'youtube_music' => true,
                'music_file' => true,
                'slideshow_background' => true,
                'video_background' => true,
                'staff' => true,
                'announcements' => true,
                'ken_burns' => true,
            ],
        ],
    ],

    'paths' => [
        // Overridden in bootstrap relative to project root
    ],
];
