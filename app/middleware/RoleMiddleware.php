<?php
/**
 * Role-Based Access Control Middleware
 * Checks if the user has the required role to access a route
 */

class RoleMiddleware {
    private $requiredRoles = [];
    
    /**
     * Constructor
     */
    public function __construct($roles = []) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        $this->requiredRoles = $roles;
    }
    
    /**
     * Handle the middleware
     */
    public function handle() {
        if (!Auth::check()) {
            $this->unauthorized('User not authenticated');
            return false;
        }
        
        $user = Auth::user();
        
        if (empty($this->requiredRoles)) {
            return true;
        }
        
        // Admin has access to everything
        if ($user['role'] === ROLE_ADMIN) {
            return true;
        }
        
        // Check if user has required role
        if (!in_array($user['role'], $this->requiredRoles)) {
            $this->forbidden('Insufficient permissions');
            return false;
        }
        
        return true;
    }
    
    /**
     * Unauthorized response
     */
    private function unauthorized($message) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
    
    /**
     * Forbidden response
     */
    private function forbidden($message) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}

