<?php
// FILE: /config/app.php

/**
 * Application Configuration
 */

return [
    'name' => 'SplashLeadRouter',
    'version' => '1.0.0',
    'timezone' => 'UTC',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',

    // Session configuration
    'session' => [
        'lifetime' => 7200, // 2 hours in seconds
        'name' => 'SPLASHLEADROUTER_SESSION',
    ],

    // Upload configuration
    'upload' => [
        'path' => __DIR__ . '/../storage/uploads/',
        'max_size' => 5242880, // 5MB in bytes
        'allowed_types' => ['csv', 'xls', 'xlsx', 'pdf'],
    ],

    // Pagination
    'pagination' => [
        'per_page' => 20,
    ],
];
