<?php
// FILE: /app/core/Router.php

/**
 * Router Class
 * Handles URL routing and dispatches requests to appropriate controllers
 */
class Router {
    private $routes = [];
    private $namedRoutes = [];

    /**
     * Add a GET route
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string $name Optional route name
     */
    public function get($path, $controller, $action, $name = null) {
        $this->addRoute('GET', $path, $controller, $action, $name);
    }

    /**
     * Add a POST route
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string $name Optional route name
     */
    public function post($path, $controller, $action, $name = null) {
        $this->addRoute('POST', $path, $controller, $action, $name);
    }

    /**
     * Add a route to the routing table
     * @param string $method
     * @param string $path
     * @param string $controller
     * @param string $action
     * @param string $name
     */
    private function addRoute($method, $path, $controller, $action, $name = null) {
        $pattern = $this->convertPathToRegex($path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    /**
     * Convert route path to regex pattern
     * @param string $path
     * @return string
     */
    private function convertPathToRegex($path) {
        // Replace :param with named capture groups
        $pattern = preg_replace('/\/:([a-zA-Z0-9_]+)/', '/(?P<$1>[a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch the current request
     */
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remove query string and trailing slash
        $requestUri = strtok($requestUri, '?');
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                // Extract parameters from URL
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Load controller
                $controllerName = $route['controller'];
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

                if (!file_exists($controllerFile)) {
                    $this->notFound();
                    return;
                }

                require_once $controllerFile;

                $controller = new $controllerName();
                $action = $route['action'];

                if (!method_exists($controller, $action)) {
                    $this->notFound();
                    return;
                }

                // Call controller action with parameters
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // No route matched
        $this->notFound();
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound() {
        http_response_code(404);
        echo '404 - Page Not Found';
    }

    /**
     * Generate URL from route name
     * @param string $name
     * @param array $params
     * @return string
     */
    public function route($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            return '/';
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace(':' . $key, $value, $path);
        }

        return $path;
    }
}
