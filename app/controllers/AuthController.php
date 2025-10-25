<?php
/**
 * Authentication Controller
 * Handles user authentication, registration, and password reset
 */

class AuthController extends Controller {
    
    /**
     * Login user
     */
    public function login() {
        $email = $this->input('email');
        $password = $this->input('password');
        
        // Validate input
        $validation = $this->validate($this->input(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Attempt login
        $result = Auth::login($email, $password);
        
        if (!$result) {
            return $this->error('Invalid credentials', 401);
        }
        
        return $this->success('Login successful', $result);
    }
    
    /**
     * Register new user
     */
    public function register() {
        $data = [
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'phone' => $this->input('phone'),
            'role' => $this->input('role', ROLE_TEAM_MEMBER)
        ];
        
        // Validate input
        $validation = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Attempt registration
        $result = Auth::register($data);
        
        if (isset($result['error'])) {
            return $this->error($result['error'], 400);
        }
        
        return $this->success('Registration successful', $result['user'], 201);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        Auth::logout();
        return $this->success('Logout successful');
    }
    
    /**
     * Get authenticated user
     */
    public function me() {
        $this->requireAuth();
        return $this->success('User retrieved', $this->user());
    }
    
    /**
     * Refresh token
     */
    public function refresh() {
        $this->requireAuth();
        $user = Auth::user();
        
        // Generate new token
        $token = Auth::generateToken($user);
        
        return $this->success('Token refreshed', [
            'token' => $token,
            'user' => $user
        ]);
    }
    
    /**
     * Forgot password - send reset link
     */
    public function forgotPassword() {
        $email = $this->input('email');
        
        $validation = $this->validate(['email' => $email], [
            'email' => 'required|email'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $token = Auth::generateResetToken($email);
        
        if (!$token) {
            return $this->error('Email not found', 404);
        }
        
        // TODO: Send email with reset link
        // For now, return the token (in production, this should be sent via email)
        
        return $this->success('Password reset link sent', [
            'token' => $token,
            'message' => 'Please check your email for password reset instructions'
        ]);
    }
    
    /**
     * Reset password
     */
    public function resetPassword() {
        $token = $this->input('token');
        $password = $this->input('password');
        $confirmPassword = $this->input('confirm_password');
        
        $validation = $this->validate($_POST, [
            'token' => 'required',
            'password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        if ($password !== $confirmPassword) {
            return $this->error('Passwords do not match', 400);
        }
        
        $result = Auth::resetPassword($token, $password);
        
        if (!$result) {
            return $this->error('Invalid or expired token', 400);
        }
        
        return $this->success('Password reset successful');
    }
}

