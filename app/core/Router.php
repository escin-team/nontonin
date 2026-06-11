<?php
/**
 * Router - PHP 5.6 - 8.3 Compatible
 * FIXED: Added resolveCallback() to handle "Controller@method" string format
 * This prevents Fatal Error on call_user_func("AuthController@login") in PHP 8.3
 * 
 * Features:
 * - Auto-load controller files
 * - Support parameterized routes with {param} syntax
 * - 404 handler with BASE_URL check
 * - Anti double-slash URL handling
 */

class Router {
    private $routes = array();
    
    /**
     * Register GET route
     * @param string $path URL path
     * @param callable|string $callback Handler function or "Controller@method" string
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Register POST route
     * @param string $path URL path
     * @param callable|string $callback Handler function or "Controller@method" string
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Resolve callback string "Controller@method" to callable array
     * CRITICAL FIX for PHP 8.3: call_user_func() no longer accepts "Class@method" string
     * @param string|callable $callback
     * @return callable|array Returns callable array [new Controller(), 'method']
     */
    private function resolveCallback($callback) {
        // If already a callable (closure), return as-is
        if (is_callable($callback)) {
            return $callback;
        }
        
        // If string in format "Controller@method", convert to callable array
        if (is_string($callback) && strpos($callback, '@') !== false) {
            $parts = explode('@', $callback, 2);
            $controllerName = $parts[0];
            $methodName = $parts[1];
            
            // Auto-load controller file if exists
            $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            }
            
            // Instantiate controller and return callable array
            $controller = new $controllerName();
            return array($controller, $methodName);
        }
        
        // Return as-is (might be invalid, but let PHP handle the error)
        return $callback;
    }
    
    /**
     * Match and dispatch route
     */
    public function dispatch() {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if exists
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $basePath = str_replace('/public', '', dirname($scriptName));
        if ($basePath !== '/' && !empty($basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove trailing slash - CRITICAL for ByetHost (prevents double-slash 404)
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            $resolved = $this->resolveCallback($callback);
            call_user_func($resolved);
            return;
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] as $route => $callback) {
            // Convert {param} to regex pattern
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Decode URL parameters (for episode_id with special chars)
                foreach ($matches as &$match) {
                    $match = urldecode($match);
                }
                unset($match);
                
                $resolved = $this->resolveCallback($callback);
                call_user_func_array($resolved, $matches);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo '<!DOCTYPE html>';
        echo '<html><head><meta charset="UTF-8"><title>404 - Page Not Found</title>';
        echo '<style>body{background:#121212;color:#e0e0e0;font-family:sans-serif;text-align:center;padding:50px;} h1{color:#e94560;} a{color:#e94560;}</style>';
        echo '</head><body>';
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The page you are looking for does not exist.</p>';
        echo '<p><a href="' . url('home') . '">Go Home</a></p>';
        echo '</body></html>';
    }
}
