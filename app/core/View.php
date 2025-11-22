<?php
// FILE: /app/core/View.php

/**
 * View Helper Class
 * Provides helper methods for views
 */
class View {

    /**
     * Escape HTML output
     * @param string $string
     * @return string
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate CSRF token
     * @return string
     */
    public static function csrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Generate CSRF input field
     * @return string
     */
    public static function csrfField() {
        $token = self::csrf();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Format date
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (empty($date)) {
            return '';
        }
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Format currency
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'USD') {
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Get flash message
     * @param string $key
     * @return string|null
     */
    public static function flash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    /**
     * Set flash message
     * @param string $key
     * @param string $message
     */
    public static function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get old input value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function old($key, $default = '') {
        if (isset($_SESSION['old'][$key])) {
            $value = $_SESSION['old'][$key];
            return $value;
        }
        return $default;
    }

    /**
     * Set old input
     * @param array $data
     */
    public static function setOld($data) {
        $_SESSION['old'] = $data;
    }

    /**
     * Clear old input
     */
    public static function clearOld() {
        unset($_SESSION['old']);
    }
}
