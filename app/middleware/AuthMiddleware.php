<?php
/**
 * Authentication Middleware
 * Checks if the user is authenticated before allowing access to protected routes
 */

class AuthMiddleware {
    private $publicRoutes = [
        '/api/v1/auth/login',
        '/api/v1/auth/register',
        '/api/v1/auth/forgot-password',
        '/api/v1/auth/reset-password',
        '/',
        '/login',
    ];
    
    /**
     * Handle the middleware
     */
    public function handle() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from URI
        $config = require ROOT_PATH . '/config/app.php';
        $basePath = parse_url($config['base_url'], PHP_URL_PATH);
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($requestUri) || $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }
        
        // Check if route is public
        if ($this->isPublicRoute($requestUri)) {
            return true;
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            // Check if it's an API request
            if (strpos($requestUri, '/api/') === 0) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to access this resource.'
                ]);
                exit;
            } else {
                // Redirect to login page
                header('Location: /login');
                exit;
            }
        }
        
        return true;
    }
    
    /**
     * Check if the route is public
     */
    private function isPublicRoute($uri) {
        foreach ($this->publicRoutes as $route) {
            if ($uri === $route || strpos($uri, $route) === 0) {
                return true;
            }
        }
        
        return false;
    }
}

