<?php
/**
 * Service Controller
 * Handles service management operations
 */

class ServiceController extends Controller {
    private $serviceModel;
    
    public function __construct() {
        parent::__construct();
        $this->serviceModel = $this->model('Service');
    }
    
    /**
     * Get all services
     */
    public function index() {
        $this->requireAuth();
        
        $isActive = $this->input('is_active');
        $category = $this->input('category');
        
        $conditions = [];
        if ($isActive !== null) {
            $conditions['is_active'] = $isActive;
        }
        if ($category) {
            $conditions['category'] = $category;
        }
        
        $services = $this->serviceModel->all($conditions, 'name ASC');
        
        // Get roles for each service
        foreach ($services as &$service) {
            $service['roles'] = $this->serviceModel->getRoles($service['id']);
        }
        
        return $this->success('Services retrieved', $services);
    }
    
    /**
     * Get single service
     */
    public function show($id) {
        $this->requireAuth();
        
        $service = $this->serviceModel->find($id);
        
        if (!$service) {
            return $this->error('Service not found', 404);
        }
        
        $service['roles'] = $this->serviceModel->getRoles($id);
        
        return $this->success('Service retrieved', $service);
    }
    
    /**
     * Create new service
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $data = [
            'name' => $this->input('name'),
            'slug' => $this->slugify($this->input('name')),
            'description' => $this->input('description'),
            'category' => $this->input('category'),
            'is_active' => $this->input('is_active', 1)
        ];
        
        $validation = $this->validate($data, [
            'name' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $serviceId = $this->serviceModel->create($data);
        
        // Add roles if provided
        $roles = $this->input('roles', []);
        if (!empty($roles)) {
            $this->serviceModel->syncRoles($serviceId, $roles);
        }
        
        $service = $this->serviceModel->find($serviceId);
        $service['roles'] = $this->serviceModel->getRoles($serviceId);
        
        return $this->success('Service created', $service, 201);
    }
    
    /**
     * Update service
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $service = $this->serviceModel->find($id);
        if (!$service) {
            return $this->error('Service not found', 404);
        }
        
        $data = [
            'name' => $this->input('name', $service['name']),
            'slug' => $this->slugify($this->input('name', $service['name'])),
            'description' => $this->input('description', $service['description']),
            'category' => $this->input('category', $service['category']),
            'is_active' => $this->input('is_active', $service['is_active'])
        ];
        
        $this->serviceModel->update($id, $data);
        
        // Update roles if provided
        $roles = $this->input('roles');
        if ($roles !== null) {
            $this->serviceModel->syncRoles($id, $roles);
        }
        
        $updatedService = $this->serviceModel->find($id);
        $updatedService['roles'] = $this->serviceModel->getRoles($id);
        
        return $this->success('Service updated', $updatedService);
    }
    
    /**
     * Delete service
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $service = $this->serviceModel->find($id);
        if (!$service) {
            return $this->error('Service not found', 404);
        }
        
        $this->serviceModel->delete($id);
        return $this->success('Service deleted');
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

