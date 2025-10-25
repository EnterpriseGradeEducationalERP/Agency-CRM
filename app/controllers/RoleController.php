<?php
/**
 * Role Controller
 * Handles job role management operations
 */

class RoleController extends Controller {
    private $roleModel;
    
    public function __construct() {
        parent::__construct();
        $this->roleModel = $this->model('JobRole');
    }
    
    /**
     * Get all roles
     */
    public function index() {
        $this->requireAuth();
        
        $isActive = $this->input('is_active');
        
        $conditions = [];
        if ($isActive !== null) {
            $conditions['is_active'] = $isActive;
        }
        
        $roles = $this->roleModel->all($conditions, 'name ASC');
        
        return $this->success('Roles retrieved', $roles);
    }
    
    /**
     * Get single role
     */
    public function show($id) {
        $this->requireAuth();
        
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return $this->error('Role not found', 404);
        }
        
        return $this->success('Role retrieved', $role);
    }
    
    /**
     * Create new role
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $data = [
            'name' => $this->input('name'),
            'slug' => $this->slugify($this->input('name')),
            'hourly_rate' => $this->input('hourly_rate', 0),
            'currency' => $this->input('currency', 'INR'),
            'description' => $this->input('description'),
            'is_active' => $this->input('is_active', 1)
        ];
        
        $validation = $this->validate($data, [
            'name' => 'required',
            'hourly_rate' => 'required|numeric'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $roleId = $this->roleModel->create($data);
        $role = $this->roleModel->find($roleId);
        
        return $this->success('Role created', $role, 201);
    }
    
    /**
     * Update role
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->error('Role not found', 404);
        }
        
        $data = [
            'name' => $this->input('name', $role['name']),
            'slug' => $this->slugify($this->input('name', $role['name'])),
            'hourly_rate' => $this->input('hourly_rate', $role['hourly_rate']),
            'currency' => $this->input('currency', $role['currency']),
            'description' => $this->input('description', $role['description']),
            'is_active' => $this->input('is_active', $role['is_active'])
        ];
        
        $this->roleModel->update($id, $data);
        $updatedRole = $this->roleModel->find($id);
        
        return $this->success('Role updated', $updatedRole);
    }
    
    /**
     * Delete role
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $role = $this->roleModel->find($id);
        if (!$role) {
            return $this->error('Role not found', 404);
        }
        
        $this->roleModel->delete($id);
        return $this->success('Role deleted');
    }
    
    /**
     * Helper: Convert string to slug
     */
    private function slugify($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}

