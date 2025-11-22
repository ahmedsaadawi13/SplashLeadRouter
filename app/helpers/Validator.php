<?php
// FILE: /app/helpers/Validator.php

/**
 * Validator Class
 * Handles input validation
 */
class Validator {
    private $errors = [];
    private $data = [];

    /**
     * Constructor
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Validate required field
     * @param string $field
     * @param string $message
     * @return self
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field][] = $message ?: ucfirst($field) . ' is required';
        }
        return $this;
    }

    /**
     * Validate email
     * @param string $field
     * @param string $message
     * @return self
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?: 'Invalid email address';
        }
        return $this;
    }

    /**
     * Validate minimum length
     * @param string $field
     * @param int $min
     * @param string $message
     * @return self
     */
    public function min($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?: ucfirst($field) . " must be at least {$min} characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     * @param string $field
     * @param int $max
     * @param string $message
     * @return self
     */
    public function max($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field][] = $message ?: ucfirst($field) . " must not exceed {$max} characters";
        }
        return $this;
    }

    /**
     * Validate numeric value
     * @param string $field
     * @param string $message
     * @return self
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?: ucfirst($field) . ' must be a number';
        }
        return $this;
    }

    /**
     * Validate value is in array
     * @param string $field
     * @param array $values
     * @param string $message
     * @return self
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field][] = $message ?: 'Invalid value for ' . $field;
        }
        return $this;
    }

    /**
     * Validate phone number (basic)
     * @param string $field
     * @param string $message
     * @return self
     */
    public function phone($field, $message = null) {
        if (isset($this->data[$field])) {
            $phone = preg_replace('/[^0-9+]/', '', $this->data[$field]);
            if (strlen($phone) < 10) {
                $this->errors[$field][] = $message ?: 'Invalid phone number';
            }
        }
        return $this;
    }

    /**
     * Custom validation with callback
     * @param string $field
     * @param callable $callback
     * @param string $message
     * @return self
     */
    public function custom($field, $callback, $message) {
        if (isset($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    /**
     * Check if validation passed
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get all errors
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Get first error for a field
     * @param string $field
     * @return string|null
     */
    public function getError($field) {
        return isset($this->errors[$field][0]) ? $this->errors[$field][0] : null;
    }

    /**
     * Sanitize input data
     * @param array $data
     * @return array
     */
    public static function sanitize($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        return $sanitized;
    }
}
