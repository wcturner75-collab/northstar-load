<?php

declare(strict_types=1);

/**
 * Copy to config.php and fill in production values.
 * Keep this file outside the web root.
 */
return [
    'app' => [
        'name' => 'Northstar Load',
        'brand' => 'Northstar Scripts',
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
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
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
        'runtime_version' => '1.0.0',
    ],

    'entitlements' => [
        'free' => [
            'max_projects' => 3,
            'max_media' => 40,
            'max_builds_per_day' => 10,
        ],
        'standard' => [
            'max_projects' => 25,
            'max_media' => 250,
            'max_builds_per_day' => 50,
        ],
        'pro' => [
            'max_projects' => 200,
            'max_media' => 2000,
            'max_builds_per_day' => 200,
        ],
    ],

    'paths' => [
        // Overridden in bootstrap relative to project root
    ],
];
