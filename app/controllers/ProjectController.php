<?php
/**
 * Project Controller
 * Handles project management operations
 */

class ProjectController extends Controller {
    private $projectModel;
    
    public function __construct() {
        parent::__construct();
        $this->projectModel = $this->model('Project');
    }
    
    /**
     * Get all projects
     */
    public function index() {
        $this->requireAuth();
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $status = $this->input('status');
        $clientId = $this->input('client_id');
        $managerId = $this->input('manager_id');
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($clientId) {
            $conditions['client_id'] = $clientId;
        }
        if ($managerId) {
            $conditions['project_manager_id'] = $managerId;
        }
        
        $result = $this->projectModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        // Add team count and task stats
        foreach ($result['data'] as &$project) {
            $project['team_count'] = $this->projectModel->getTeamCount($project['id']);
            $project['task_stats'] = $this->projectModel->getTaskStats($project['id']);
        }
        
        return $this->success('Projects retrieved', $result);
    }
    
    /**
     * Get single project
     */
    public function show($id) {
        $this->requireAuth();
        
        $project = $this->projectModel->find($id);
        
        if (!$project) {
            return $this->error('Project not found', 404);
        }
        
        $project['client'] = $this->projectModel->getClient($id);
        $project['manager'] = $this->projectModel->getManager($id);
        $project['team'] = $this->projectModel->getTeam($id);
        $project['task_stats'] = $this->projectModel->getTaskStats($id);
        $project['time_stats'] = $this->projectModel->getTimeStats($id);
        
        return $this->success('Project retrieved', $project);
    }
    
    /**
     * Create new project
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $data = [
            'name' => $this->input('name'),
            'code' => $this->input('code', $this->generateProjectCode()),
            'client_id' => $this->input('client_id'),
            'deal_id' => $this->input('deal_id'),
            'quote_id' => $this->input('quote_id'),
            'description' => $this->input('description'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'budget' => $this->input('budget', 0),
            'currency' => $this->input('currency', 'INR'),
            'status' => $this->input('status', PROJECT_PLANNED),
            'progress' => 0,
            'project_manager_id' => $this->input('project_manager_id', Auth::id()),
            'priority' => $this->input('priority', PRIORITY_MEDIUM),
            'notes' => $this->input('notes')
        ];
        
        $validation = $this->validate($data, [
            'name' => 'required',
            'client_id' => 'required',
            'budget' => 'required|numeric'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $projectId = $this->projectModel->create($data);
        
        // Add team members if provided
        $team = $this->input('team', []);
        foreach ($team as $member) {
            $this->projectModel->addTeamMember($projectId, [
                'user_id' => $member['user_id'],
                'role' => $member['role'] ?? null,
                'allocation_percentage' => $member['allocation_percentage'] ?? 100,
                'hourly_rate' => $member['hourly_rate'] ?? null,
                'joined_at' => date('Y-m-d')
            ]);
        }
        
        $project = $this->projectModel->find($projectId);
        $project['team'] = $this->projectModel->getTeam($projectId);
        
        return $this->success('Project created', $project, 201);
    }
    
    /**
     * Update project
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->error('Project not found', 404);
        }
        
        $data = [
            'name' => $this->input('name', $project['name']),
            'description' => $this->input('description', $project['description']),
            'start_date' => $this->input('start_date', $project['start_date']),
            'end_date' => $this->input('end_date', $project['end_date']),
            'budget' => $this->input('budget', $project['budget']),
            'currency' => $this->input('currency', $project['currency']),
            'status' => $this->input('status', $project['status']),
            'progress' => $this->input('progress', $project['progress']),
            'project_manager_id' => $this->input('project_manager_id', $project['project_manager_id']),
            'priority' => $this->input('priority', $project['priority']),
            'notes' => $this->input('notes', $project['notes'])
        ];
        
        $this->projectModel->update($id, $data);
        $updatedProject = $this->projectModel->find($id);
        
        return $this->success('Project updated', $updatedProject);
    }
    
    /**
     * Delete project
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->error('Project not found', 404);
        }
        
        $this->projectModel->delete($id);
        return $this->success('Project deleted');
    }
    
    /**
     * Add team member
     */
    public function addTeamMember($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->error('Project not found', 404);
        }
        
        $data = [
            'user_id' => $this->input('user_id'),
            'role' => $this->input('role'),
            'allocation_percentage' => $this->input('allocation_percentage', 100),
            'hourly_rate' => $this->input('hourly_rate'),
            'joined_at' => date('Y-m-d')
        ];
        
        $validation = $this->validate($data, [
            'user_id' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $this->projectModel->addTeamMember($id, $data);
        $team = $this->projectModel->getTeam($id);
        
        return $this->success('Team member added', $team);
    }
    
    /**
     * Remove team member
     */
    public function removeTeamMember($id, $userId) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]);
        
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->error('Project not found', 404);
        }
        
        $this->projectModel->removeTeamMember($id, $userId);
        return $this->success('Team member removed');
    }
    
    /**
     * Generate project code
     */
    private function generateProjectCode() {
        $settingsModel = $this->model('Setting');
        $prefix = $settingsModel->getValue('project_prefix', 'PRJ');
        $year = date('Y');
        
        // Get last project for this year
        $sql = "SELECT code FROM projects 
                WHERE code LIKE :pattern 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}-%";
        $lastProject = $this->db->fetchOne($sql, ['pattern' => $pattern]);
        
        if ($lastProject) {
            $lastNumber = (int) substr($lastProject['code'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("%s-%s-%04d", $prefix, $year, $newNumber);
    }
}

