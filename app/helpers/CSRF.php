<?php
// FILE: /app/helpers/CSRF.php

/**
 * CSRF Protection Class
 * Handles CSRF token generation and validation
 */
class CSRF {

    /**
     * Generate CSRF token
     * @return string
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token
     * @return bool
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Verify CSRF token from POST request
     * @return bool
     */
    public static function verify() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            return self::validateToken($token);
        }
        return true;
    }

    /**
     * Require valid CSRF token or die
     */
    public static function requireToken() {
        if (!self::verify()) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}
