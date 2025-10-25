<?php
/**
 * User Model
 * Handles user data operations
 */

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'email', 
        'password', 
        'first_name', 
        'last_name', 
        'phone', 
        'role', 
        'avatar', 
        'status',
        'last_login'
    ];
    protected $timestamps = true;
    
    /**
     * Get user by email
     */
    public function findByEmail($email) {
        return $this->findWhere(['email' => $email]);
    }
    
    /**
     * Get users by role
     */
    public function findByRole($role) {
        return $this->all(['role' => $role, 'status' => 'active']);
    }
    
    /**
     * Get active users
     */
    public function getActive() {
        return $this->all(['status' => 'active'], 'first_name ASC');
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($userId) {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }
}

