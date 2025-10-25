<?php
/**
 * Base Controller Class
 * All controllers extend from this class
 */

class Controller {
    protected $db;
    protected $config;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = require __DIR__ . '/../config/app.php';
    }
    
    /**
     * Load view
     */
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../app/views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("View not found: {$view}");
        }
    }
    
    /**
     * Load model
     */
    protected function model($model) {
        $modelFile = __DIR__ . '/../app/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        
        throw new Exception("Model not found: {$model}");
    }
    
    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        $requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? bin2hex(random_bytes(8));
        header('X-Request-Id: ' . $requestId);
        if (is_array($data) && !isset($data['request_id'])) {
            $data['request_id'] = $requestId;
        }
        echo json_encode($data);
        exit;
    }
    
    /**
     * Success response
     */
    protected function success($message, $data = null, $statusCode = 200) {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        $this->json($response, $statusCode);
    }
    
    /**
     * Error response
     */
    protected function error($message, $statusCode = 400, $errors = null) {
        $response = ['success' => false, 'message' => $message];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        $this->json($response, $statusCode);
    }
    
    /**
     * Validate request data
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);
            
            foreach ($ruleList as $rule) {
                if ($rule === 'required' && empty($data[$field])) {
                    $errors[$field][] = ucfirst($field) . ' is required';
                }
                
                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($data[$field] ?? '') < $min) {
                        $errors[$field][] = ucfirst($field) . " must be at least {$min} characters";
                    }
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($data[$field] ?? '') > $max) {
                        $errors[$field][] = ucfirst($field) . " must not exceed {$max} characters";
                    }
                }
                
                if ($rule === 'email' && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = ucfirst($field) . ' must be a valid email address';
                }
                
                if ($rule === 'numeric' && !empty($data[$field]) && !is_numeric($data[$field])) {
                    $errors[$field][] = ucfirst($field) . ' must be a number';
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Get sanitized pagination params (page, perPage)
     */
    protected function paginationParams($defaultPerPage = null) {
        $page = (int) ($this->input('page', 1));
        $perPage = (int) ($this->input('per_page', $defaultPerPage ?? ($this->config['items_per_page'] ?? 20)));
        $page = max(1, $page);
        $max = (int) ($this->config['max_items_per_page'] ?? 100);
        if ($perPage < 1) { $perPage = 1; }
        if ($perPage > $max) { $perPage = $max; }
        return [$page, $perPage];
    }
    
    /**
     * Get request input
     */
    protected function input($key = null, $default = null) {
        $input = array_merge($_GET, $_POST);
        
        // Handle JSON input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $json = json_decode(file_get_contents('php://input'), true);
            if ($json) {
                $input = array_merge($input, $json);
            }
        }
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }
    
    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * Get authenticated user
     */
    protected function user() {
        return Auth::user();
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return Auth::check();
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->error('Unauthorized', 401);
        }
    }
    
    /**
     * Check user role
     */
    protected function hasRole($role) {
        $user = $this->user();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Require specific role
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $user = $this->user();
        if (!in_array($user['role'], $roles)) {
            $this->error('Forbidden: Insufficient permissions', 403);
        }
    }
}

