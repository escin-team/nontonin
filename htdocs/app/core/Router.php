<?php
/**
 * Simple Router - PHP 5.5/5.6 Compatible
 * Handles URL parsing and controller dispatching
 * 
 * Rules Applied:
 * - No null coalescing (??)
 * - No arrow functions (fn())
 * - No scalar type declarations
 * - No anonymous classes
 */

class Router {
    private $routes = array();
    
    /**
     * Register GET route
     * @param string $path URL path
     * @param mixed $callback Handler function or Controller@Method string
     */
    public function get($path, $callback) {
        if (!isset($this->routes['GET'])) {
            $this->routes['GET'] = array();
        }
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Register POST route
     * @param string $path URL path
     * @param mixed $callback Handler function or Controller@Method string
     */
    public function post($path, $callback) {
        if (!isset($this->routes['POST'])) {
            $this->routes['POST'] = array();
        }
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Match and dispatch route
     */
    public function dispatch() {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if exists
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Check for exact match
        if (isset($this->routes[$method]) && isset($this->routes[$method][$uri])) {
            $this->callHandler($this->routes[$method][$uri]);
            return;
        }
        
        // Check for parameterized routes
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';
                
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    $this->callHandler($callback, $matches);
                    return;
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo '<div style="text-align:center;padding:50px;font-family:Arial,sans-serif;">';
        echo '<h1 style="color:#e74c3c;">404 - Page Not Found</h1>';
        echo '<p>The page you are looking for does not exist.</p>';
        echo '<a href="' . BASE_URL . '" style="color:#3498db;">Go Home</a>';
        echo '</div>';
    }
    
    /**
     * Call handler (function or Controller@Method)
     * @param mixed $callback Function or Controller@Method string
     * @param array $params Parameters to pass
     */
    private function callHandler($callback, $params = array()) {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            // Controller@Method format
            list($controller, $method) = explode('@', $callback, 2);
            
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    if (!empty($params)) {
                        call_user_func_array(array($instance, $method), $params);
                    } else {
                        call_user_func(array($instance, $method));
                    }
                    return;
                }
            }
            throw new Exception('Controller or method not found: ' . $controller . '@' . $method);
        } elseif (is_callable($callback)) {
            // Closure/function format
            if (!empty($params)) {
                call_user_func_array($callback, $params);
            } else {
                call_user_func($callback);
            }
            return;
        }
        
        throw new Exception('Invalid route handler');
    }
}
