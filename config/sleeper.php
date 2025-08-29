<?php

return [
    // Base API URL for Sleeper v1
    'base_url' => env('SLEEPER_BASE_URL', 'https://api.sleeper.app/v1'),

    // CDN base for avatars
    'cdn_url' => env('SLEEPER_CDN_URL', 'https://sleepercdn.com'),

    // Default sport (Sleeper currently supports nfl for this API)
    'default_sport' => env('SLEEPER_DEFAULT_SPORT', 'nfl'),

    // HTTP client options
    'timeout' => env('SLEEPER_TIMEOUT', 30),
    'connect_timeout' => env('SLEEPER_CONNECT_TIMEOUT', 10),
    'retry' => [
        'times' => env('SLEEPER_RETRY_TIMES', 0),
        'sleep' => env('SLEEPER_RETRY_SLEEP', 100), // milliseconds
    ],

    // Local cache of Sleeper "all players" dataset in CSV form
    // Defaults to storage/app/sleeper/players.csv but can be overridden
    'players' => [
        'csv_path' => env('SLEEPER_PLAYERS_CSV', storage_path('app/sleeper/players.csv')),
    ],
];
