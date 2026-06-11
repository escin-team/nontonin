<?php
/**
 * Simple Router
 * PHP 5.6 Compatible
 */

class Router {
    private $routes = array();
    
    /**
     * Register GET route
     * @param string $path URL path
     * @param callable $callback Handler function
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Register POST route
     * @param string $path URL path
     * @param callable $callback Handler function
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Match and dispatch route
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if exists
        $basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
        if ($basePath !== '/') {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
            return;
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] as $route => $callback) {
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($callback, $matches);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo '<div style="text-align:center;padding:50px;">';
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The page you are looking for does not exist.</p>';
        echo '<a href="' . BASE_URL . '">Go Home</a>';
        echo '</div>';
    }
}
