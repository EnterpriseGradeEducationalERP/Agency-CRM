<?php
/**
 * User Controller
 * Handles user management operations
 */

class UserController extends Controller {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }
    
    /**
     * Get all users
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $role = $this->input('role');
        $status = $this->input('status', 'active');
        
        $conditions = [];
        if ($role) {
            $conditions['role'] = $role;
        }
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $result = $this->userModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        // Remove passwords
        foreach ($result['data'] as &$user) {
            unset($user['password']);
        }
        
        return $this->success('Users retrieved', $result);
    }
    
    /**
     * Get single user
     */
    public function show($id) {
        $this->requireAuth();
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        unset($user['password']);
        return $this->success('User retrieved', $user);
    }
    
    /**
     * Create new user
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $data = [
            'email' => $this->input('email'),
            'password' => password_hash($this->input('password'), PASSWORD_BCRYPT),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'phone' => $this->input('phone'),
            'role' => $this->input('role', ROLE_TEAM_MEMBER),
            'status' => $this->input('status', 'active')
        ];
        
        $validation = $this->validate($data, [
            'email' => 'required|email',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Check if email exists
        $existing = $this->userModel->findWhere(['email' => $data['email']]);
        if ($existing) {
            return $this->error('Email already exists', 400);
        }
        
        $userId = $this->userModel->create($data);
        $user = $this->userModel->find($userId);
        unset($user['password']);
        
        return $this->success('User created', $user, 201);
    }
    
    /**
     * Update user
     */
    public function update($id) {
        $this->requireAuth();
        
        // Users can update themselves, admins can update anyone
        if (!$this->hasRole(ROLE_ADMIN) && Auth::id() != $id) {
            return $this->error('Forbidden', 403);
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        $data = [
            'first_name' => $this->input('first_name', $user['first_name']),
            'last_name' => $this->input('last_name', $user['last_name']),
            'phone' => $this->input('phone', $user['phone']),
            'avatar' => $this->input('avatar', $user['avatar'])
        ];
        
        // Only admins can change role and status
        if ($this->hasRole(ROLE_ADMIN)) {
            $data['role'] = $this->input('role', $user['role']);
            $data['status'] = $this->input('status', $user['status']);
        }
        
        // Update password if provided
        if ($this->input('password')) {
            $data['password'] = password_hash($this->input('password'), PASSWORD_BCRYPT);
        }
        
        $this->userModel->update($id, $data);
        $updatedUser = $this->userModel->find($id);
        unset($updatedUser['password']);
        
        return $this->success('User updated', $updatedUser);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->error('User not found', 404);
        }
        
        // Prevent deleting yourself
        if (Auth::id() == $id) {
            return $this->error('Cannot delete yourself', 400);
        }
        
        $this->userModel->delete($id);
        return $this->success('User deleted');
    }
}

