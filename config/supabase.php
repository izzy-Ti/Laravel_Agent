<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for Supabase Postgres, REST API, Auth & Storage.
    |
    */
    'url' => env('SUPABASE_URL', 'https://your-project-ref.supabase.co'),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY', ''),
    
    'database' => [
        'host' => env('DB_HOST', 'aws-0-us-east-1.pooler.supabase.com'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'postgres'),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD', ''),
        'sslmode' => env('DB_SSLMODE', 'require'),
    ],
];
