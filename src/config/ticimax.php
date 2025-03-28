<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ticimax API Configuration
    |--------------------------------------------------------------------------
    |
    | When using this package in a Laravel application, you can override
    | these settings using environment variables in your .env file:
    |
    | TICIMAX_BASE_URL=your-api-url
    | TICIMAX_API_KEY=your-api-key
    | TICIMAX_TIMEOUT=30
    | TICIMAX_RETRY_TIMES=3
    | TICIMAX_RETRY_SLEEP=100
    |
    */

    // Base URL for the Ticimax API
    'base_url' => 'https://ticimaxwebservice.azurewebsites.net',
    
    // Your Ticimax API key
    'api_key' => '',
    
    // Request timeout in seconds
    'timeout' => 30,
    
    // Number of retry attempts for failed requests
    'retry_times' => 3,
    
    // Milliseconds to wait between retries
    'retry_sleep' => 100,
];