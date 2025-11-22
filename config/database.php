<?php
// FILE: /config/database.php

/**
 * Database Configuration
 * Load database credentials from environment variables or use defaults
 */

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'database' => getenv('DB_NAME') ?: 'splashleadrouter',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
