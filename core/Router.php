<?php
/**
 * Router Class
 * Handles routing and URL management
 */

class Router {
    private $routes = [];
    private $middlewares = [];
    private $allowedMethodsForPath = [];
    
    /**
     * Add GET route
     */
    public function get($path, $callback, $middlewares = []) {
        $this->addRoute('GET', $path, $callback, $middlewares);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $callback, $middlewares = []) {
        $this->addRoute('POST', $path, $callback, $middlewares);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $callback, $middlewares = []) {
        $this->addRoute('PUT', $path, $callback, $middlewares);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $callback, $middlewares = []) {
        $this->addRoute('DELETE', $path, $callback, $middlewares);
    }
    
    /**
     * Add route
     */
    private function addRoute($method, $path, $callback, $middlewares = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
        $this->allowedMethodsForPath[$path] = $this->allowedMethodsForPath[$path] ?? [];
        $this->allowedMethodsForPath[$path][$method] = true;
    }
    
    /**
     * Add global middleware
     */
    public function middleware($middleware) {
        $this->middlewares[] = $middleware;
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from URI
        $basePath = parse_url($this->getConfig()['base_url'], PHP_URL_PATH);
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($requestUri) || $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }
        
        $matchedPath = null;
        $matchedMethods = [];
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Run global middlewares
                foreach ($this->middlewares as $middleware) {
                    $result = $this->runMiddleware($middleware);
                    if ($result !== true) {
                        return;
                    }
                }
                
                // Run route-specific middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $result = $this->runMiddleware($middleware);
                    if ($result !== true) {
                        return;
                    }
                }
                
                // Execute callback
                return $this->executeCallback($route['callback'], $matches);
            } elseif (preg_match($pattern, $requestUri)) {
                // Path matched but method not allowed, collect allowed methods
                $matchedPath = $route['path'];
                $matchedMethods[$route['method']] = true;
            }
        }
        
        if (!empty($matchedMethods)) {
            // 405 Method Not Allowed
            $this->methodNotAllowed(array_keys($matchedMethods));
            return;
        }
        // No route found
        $this->notFound();
    }
    
    /**
     * Convert route path to regex
     */
    private function convertToRegex($path) {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Run middleware
     */
    private function runMiddleware($middleware) {
        if (is_callable($middleware)) {
            return call_user_func($middleware);
        } elseif (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            return $instance->handle();
        }
        return true;
    }
    
    /**
     * Execute callback
     */
    private function executeCallback($callback, $params = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        } elseif (is_string($callback)) {
            list($controller, $method) = explode('@', $callback);
            
            $controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $instance = new $controller();
                return call_user_func_array([$instance, $method], $params);
            }
        }
    }
    
    /**
     * 404 Not Found
     */
    private function notFound() {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Route not found']);
    }

    /**
     * 405 Method Not Allowed
     */
    private function methodNotAllowed($allowedMethods) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowedMethods));
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed',
            'allowed' => array_values($allowedMethods)
        ]);
    }
    
    /**
     * Get configuration
     */
    private function getConfig() {
        return require __DIR__ . '/../config/app.php';
    }
}

