<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('DEVLOGGER_DASHBOARD_PREFIX', 'devlogger'),
    
    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'web',
        'devlogger.dashboard'
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Access Control
    |--------------------------------------------------------------------------
    | Environments where the dashboard should be accessible
    */
    'allowed_environments' => [
        'local',
        'staging',
        'development'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    */
    'require_auth' => env('DEVLOGGER_DASHBOARD_AUTH', true),
    
    /*
    |--------------------------------------------------------------------------
    | Authorization Gate
    |--------------------------------------------------------------------------
    | Optional gate to check for dashboard access
    */
    'authorization_gate' => env('DEVLOGGER_DASHBOARD_GATE', null),

    /*
    |--------------------------------------------------------------------------
    | IP Allowlist
    |--------------------------------------------------------------------------
    | Optional IP addresses that are allowed to access the dashboard
    */
    'allowed_ips' => env('DEVLOGGER_DASHBOARD_IPS') ? explode(',', env('DEVLOGGER_DASHBOARD_IPS')) : [],

    /*
    |--------------------------------------------------------------------------
    | IDE Configuration
    |--------------------------------------------------------------------------
    */
    'ide' => [
        'default' => env('DEVLOGGER_IDE', 'vscode'),
        'handlers' => [
            'vscode' => 'vscode://file/{file}:{line}',
            'phpstorm' => 'phpstorm://open?file={file}&line={line}',
            'sublime' => 'subl://open?url=file://{file}&line={line}',
            'atom' => 'atom://core/open/file?filename={file}&line={line}',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | File Path Configuration
    |--------------------------------------------------------------------------
    */
    'file_path' => [
        'base_path' => env('DEVLOGGER_BASE_PATH', base_path()),
        'show_preview' => env('DEVLOGGER_SHOW_FILE_PREVIEW', true),
        'preview_lines' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'per_page' => 25,
        'max_per_page' => 100,
        'auto_refresh' => env('DEVLOGGER_AUTO_REFRESH', false),
        'refresh_interval' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'theme' => env('DEVLOGGER_THEME', 'dark'),
        'brand_name' => env('DEVLOGGER_BRAND_NAME', 'DevLogger Dashboard'),
        'show_user_info' => true,
    ]
];