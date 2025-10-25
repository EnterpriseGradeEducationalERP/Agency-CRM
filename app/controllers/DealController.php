<?php
/**
 * Deal Controller
 * Handles sales pipeline and deal management
 */

class DealController extends Controller {
    private $dealModel;
    private $stageModel;
    
    public function __construct() {
        parent::__construct();
        $this->dealModel = $this->model('Deal');
        $this->stageModel = $this->model('PipelineStage');
    }
    
    /**
     * Get pipeline view (Kanban)
     */
    public function pipeline() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $stages = $this->stageModel->all(['is_active' => 1], 'order_position ASC');
        
        foreach ($stages as &$stage) {
            $stage['deals'] = $this->dealModel->getByStage($stage['id']);
            $stage['total_value'] = $this->dealModel->getTotalValueByStage($stage['id']);
            $stage['count'] = count($stage['deals']);
        }
        
        return $this->success('Pipeline retrieved', $stages);
    }
    
    /**
     * Get all deals
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $status = $this->input('status', 'open');
        $stageId = $this->input('stage_id');
        $assignedTo = $this->input('assigned_to');
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($stageId) {
            $conditions['stage_id'] = $stageId;
        }
        if ($assignedTo) {
            $conditions['assigned_to'] = $assignedTo;
        }
        
        $result = $this->dealModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Deals retrieved', $result);
    }
    
    /**
     * Get single deal
     */
    public function show($id) {
        $this->requireAuth();
        
        $deal = $this->dealModel->find($id);
        
        if (!$deal) {
            return $this->error('Deal not found', 404);
        }
        
        $deal['client'] = $this->dealModel->getClient($id);
        $deal['stage'] = $this->dealModel->getStage($id);
        $deal['activities'] = $this->dealModel->getActivities($id);
        
        return $this->success('Deal retrieved', $deal);
    }
    
    /**
     * Create new deal
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        // Get default stage (Lead)
        $defaultStage = $this->stageModel->findWhere(['slug' => 'lead']);
        
        $data = [
            'title' => $this->input('title'),
            'client_id' => $this->input('client_id'),
            'stage_id' => $this->input('stage_id', $defaultStage['id']),
            'value' => $this->input('value', 0),
            'currency' => $this->input('currency', 'INR'),
            'probability' => $this->input('probability', 50),
            'expected_close_date' => $this->input('expected_close_date'),
            'assigned_to' => $this->input('assigned_to', Auth::id()),
            'next_followup' => $this->input('next_followup'),
            'description' => $this->input('description'),
            'notes' => $this->input('notes'),
            'status' => 'open'
        ];
        
        $validation = $this->validate($data, [
            'title' => 'required',
            'client_id' => 'required',
            'value' => 'required|numeric'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $dealId = $this->dealModel->create($data);
        $deal = $this->dealModel->find($dealId);
        
        return $this->success('Deal created', $deal, 201);
    }
    
    /**
     * Update deal
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $deal = $this->dealModel->find($id);
        if (!$deal) {
            return $this->error('Deal not found', 404);
        }
        
        $data = [
            'title' => $this->input('title', $deal['title']),
            'value' => $this->input('value', $deal['value']),
            'currency' => $this->input('currency', $deal['currency']),
            'probability' => $this->input('probability', $deal['probability']),
            'expected_close_date' => $this->input('expected_close_date', $deal['expected_close_date']),
            'assigned_to' => $this->input('assigned_to', $deal['assigned_to']),
            'next_followup' => $this->input('next_followup', $deal['next_followup']),
            'description' => $this->input('description', $deal['description']),
            'notes' => $this->input('notes', $deal['notes']),
            'status' => $this->input('status', $deal['status'])
        ];
        
        // Handle lost reason
        if ($data['status'] === 'lost') {
            $data['lost_reason'] = $this->input('lost_reason');
            $data['actual_close_date'] = date('Y-m-d');
        }
        
        // Handle won
        if ($data['status'] === 'won') {
            $data['actual_close_date'] = date('Y-m-d');
        }
        
        $this->dealModel->update($id, $data);
        $updatedDeal = $this->dealModel->find($id);
        
        return $this->success('Deal updated', $updatedDeal);
    }
    
    /**
     * Update deal stage (for Kanban drag-drop)
     */
    public function updateStage($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $deal = $this->dealModel->find($id);
        if (!$deal) {
            return $this->error('Deal not found', 404);
        }
        
        $stageId = $this->input('stage_id');
        if (!$stageId) {
            return $this->error('Stage ID is required', 400);
        }
        
        $stage = $this->stageModel->find($stageId);
        if (!$stage) {
            return $this->error('Stage not found', 404);
        }
        
        // Update stage
        $this->dealModel->update($id, ['stage_id' => $stageId]);
        
        // Auto-update status based on stage
        if ($stage['slug'] === 'won') {
            $this->dealModel->update($id, [
                'status' => 'won',
                'actual_close_date' => date('Y-m-d')
            ]);
        } elseif ($stage['slug'] === 'lost') {
            $this->dealModel->update($id, [
                'status' => 'lost',
                'actual_close_date' => date('Y-m-d')
            ]);
        }
        
        $updatedDeal = $this->dealModel->find($id);
        return $this->success('Deal stage updated', $updatedDeal);
    }
    
    /**
     * Delete deal
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $deal = $this->dealModel->find($id);
        if (!$deal) {
            return $this->error('Deal not found', 404);
        }
        
        $this->dealModel->delete($id);
        return $this->success('Deal deleted');
    }
}

