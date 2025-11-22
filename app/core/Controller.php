<?php
// FILE: /app/core/Controller.php

/**
 * Base Controller Class
 * All controllers extend this class
 */
class Controller {

    /**
     * Load a model
     * @param string $model
     * @return object
     */
    protected function model($model) {
        $modelFile = __DIR__ . '/../models/' . $model . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }

        die('Model not found: ' . $model);
    }

    /**
     * Load a view
     * @param string $view
     * @param array $data
     */
    protected function view($view, $data = []) {
        extract($data);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die('View not found: ' . $view);
        }
    }

    /**
     * Redirect to a URL
     * @param string $url
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    /**
     * Get current user ID
     * @return int|null
     */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current tenant ID
     * @return int|null
     */
    protected function getCurrentTenantId() {
        return $_SESSION['tenant_id'] ?? null;
    }

    /**
     * Get current user role
     * @return string|null
     */
    protected function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user has role
     * @param string|array $roles
     * @return bool
     */
    protected function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = $this->getCurrentUserRole();
        return in_array($userRole, $roles);
    }

    /**
     * Require specific role
     * @param string|array $roles
     */
    protected function requireRole($roles) {
        $this->requireAuth();

        if (!$this->hasRole($roles)) {
            http_response_code(403);
            die('Access Denied');
        }
    }
}
