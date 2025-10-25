<?php
/**
 * Authentication Class
 * Handles user authentication, JWT tokens, and Supabase integration
 */

class Auth {
    private static $user = null;
    private static $config = null;
    private static $supabaseConfig = null;
    
    /**
     * Initialize authentication
     */
    public static function init() {
        self::$config = require __DIR__ . '/../config/app.php';
        self::$supabaseConfig = require __DIR__ . '/../config/supabase.php';
        
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check for JWT token
        self::checkToken();
    }
    
    /**
     * Check for valid JWT token
     */
    private static function checkToken() {
        $token = self::getTokenFromRequest();
        
        if ($token) {
            $user = self::verifyToken($token);
            if ($user) {
                self::$user = $user;
            }
        }
    }
    
    /**
     * Get token from request
     */
    private static function getTokenFromRequest() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        // Check session
        if (isset($_SESSION['auth_token'])) {
            return $_SESSION['auth_token'];
        }
        
        // Check cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        
        return null;
    }
    
    /**
     * Login user
     */
    public static function login($email, $password) {
        $db = Database::getInstance();
        
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1",
            ['email' => $email]
        );
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // Update last login
        $db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $user['id']]
        );
        
        // Generate JWT token
        $token = self::generateToken($user);
        
        // Store in session
        $_SESSION['auth_token'] = $token;
        $_SESSION['user_id'] = $user['id'];
        
        // Log activity
        self::logActivity($user['id'], 'login', 'User logged in');
        
        return [
            'user' => self::sanitizeUser($user),
            'token' => $token
        ];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        if (self::$user) {
            self::logActivity(self::$user['id'], 'logout', 'User logged out');
        }
        
        self::$user = null;
        
        // Clear session
        $_SESSION = [];
        session_destroy();
        
        // Clear cookie
        if (isset($_COOKIE['auth_token'])) {
            setcookie('auth_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    /**
     * Register new user
     */
    public static function register($data) {
        $db = Database::getInstance();
        
        // Check if email exists
        $existing = $db->fetchOne(
            "SELECT id FROM users WHERE email = :email LIMIT 1",
            ['email' => $data['email']]
        );
        
        if ($existing) {
            return ['error' => 'Email already exists'];
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Create user
        $userId = $db->insert('users', $data);
        
        if ($userId) {
            $user = $db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
            return ['success' => true, 'user' => self::sanitizeUser($user)];
        }
        
        return ['error' => 'Registration failed'];
    }
    
    /**
     * Generate JWT token
     */
    private static function generateToken($user) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + self::$config['jwt_expiry']
        ]));
        
        $signature = hash_hmac(
            'sha256',
            "{$header}.{$payload}",
            self::$config['jwt_secret'],
            true
        );
        $signature = base64_encode($signature);
        
        return "{$header}.{$payload}.{$signature}";
    }
    
    /**
     * Verify JWT token
     */
    private static function verifyToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verify signature
        $expectedSignature = base64_encode(hash_hmac(
            'sha256',
            "{$header}.{$payload}",
            self::$config['jwt_secret'],
            true
        ));
        
        if ($signature !== $expectedSignature) {
            return false;
        }
        
        // Decode payload
        $data = json_decode(base64_decode($payload), true);
        
        // Check expiration
        if ($data['exp'] < time()) {
            return false;
        }
        
        // Get user from database
        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE id = :id AND status = 'active' LIMIT 1",
            ['id' => $data['user_id']]
        );
        
        return $user ?: false;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check() {
        return self::$user !== null;
    }
    
    /**
     * Get authenticated user
     */
    public static function user() {
        return self::$user ? self::sanitizeUser(self::$user) : null;
    }
    
    /**
     * Get user ID
     */
    public static function id() {
        return self::$user['id'] ?? null;
    }
    
    /**
     * Check if user has role
     */
    public static function hasRole($role) {
        if (!self::$user) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array(self::$user['role'], $role);
        }
        
        return self::$user['role'] === $role;
    }
    
    /**
     * Sanitize user data (remove sensitive fields)
     */
    private static function sanitizeUser($user) {
        unset($user['password']);
        return $user;
    }
    
    /**
     * Log user activity
     */
    private static function logActivity($userId, $action, $description) {
        $db = Database::getInstance();
        
        $db->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Password reset - generate token
     */
    public static function generateResetToken($email) {
        $db = Database::getInstance();
        
        $user = $db->fetchOne(
            "SELECT id FROM users WHERE email = :email AND status = 'active' LIMIT 1",
            ['email' => $email]
        );
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $db->insert('password_resets', [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $token;
    }
    
    /**
     * Reset password
     */
    public static function resetPassword($token, $newPassword) {
        $db = Database::getInstance();
        
        $reset = $db->fetchOne(
            "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1",
            ['token' => $token]
        );
        
        if (!$reset) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $db->update(
            'users',
            ['password' => $hashedPassword],
            'email = :email',
            ['email' => $reset['email']]
        );
        
        // Delete used token
        $db->delete('password_resets', 'token = :token', ['token' => $token]);
        
        return true;
    }
}

// Initialize auth on load
Auth::init();

