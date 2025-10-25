<?php
/**
 * Time Log Controller
 * Handles time tracking operations
 */

class TimeLogController extends Controller {
    private $timeLogModel;
    
    public function __construct() {
        parent::__construct();
        $this->timeLogModel = $this->model('TimeLog');
    }
    
    /**
     * Get all time logs (filtered)
     */
    public function index() {
        $this->requireAuth();
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 50);
        $userId = $this->input('user_id', Auth::id());
        $projectId = $this->input('project_id');
        $taskId = $this->input('task_id');
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        
        $conditions = [];
        if ($userId) {
            $conditions['user_id'] = $userId;
        }
        if ($projectId) {
            $conditions['project_id'] = $projectId;
        }
        if ($taskId) {
            $conditions['task_id'] = $taskId;
        }
        
        $result = $this->timeLogModel->paginate($page, $perPage, $conditions, 'start_time DESC');
        
        return $this->success('Time logs retrieved', $result);
    }
    
    /**
     * Get single time log
     */
    public function show($id) {
        $this->requireAuth();
        
        $timeLog = $this->timeLogModel->find($id);
        
        if (!$timeLog) {
            return $this->error('Time log not found', 404);
        }
        
        // Check permission
        if (!$this->hasRole(ROLE_ADMIN) && !$this->hasRole(ROLE_PROJECT_MANAGER) && $timeLog['user_id'] != Auth::id()) {
            return $this->error('Forbidden', 403);
        }
        
        return $this->success('Time log retrieved', $timeLog);
    }
    
    /**
     * Start timer
     */
    public function start() {
        $this->requireAuth();
        
        $projectId = $this->input('project_id');
        $taskId = $this->input('task_id');
        $description = $this->input('description');
        
        $validation = $this->validate($this->input(), [
            'project_id' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Check if user has active timer
        $activeTimer = $this->timeLogModel->getActiveTimer(Auth::id());
        if ($activeTimer) {
            return $this->error('You already have an active timer running', 400, $activeTimer);
        }
        
        // Get hourly rate from project team
        $hourlyRate = $this->timeLogModel->getUserHourlyRate(Auth::id(), $projectId);
        
        $data = [
            'user_id' => Auth::id(),
            'project_id' => $projectId,
            'task_id' => $taskId,
            'description' => $description,
            'start_time' => date('Y-m-d H:i:s'),
            'is_billable' => 1,
            'hourly_rate' => $hourlyRate,
            'is_manual' => 0
        ];
        
        $timeLogId = $this->timeLogModel->create($data);
        $timeLog = $this->timeLogModel->find($timeLogId);
        
        return $this->success('Timer started', $timeLog, 201);
    }
    
    /**
     * Stop timer
     */
    public function stop($id) {
        $this->requireAuth();
        
        $timeLog = $this->timeLogModel->find($id);
        
        if (!$timeLog) {
            return $this->error('Time log not found', 404);
        }
        
        // Check permission
        if ($timeLog['user_id'] != Auth::id()) {
            return $this->error('Forbidden: You can only stop your own timer', 403);
        }
        
        if ($timeLog['end_time']) {
            return $this->error('Timer already stopped', 400);
        }
        
        $endTime = date('Y-m-d H:i:s');
        $startTime = strtotime($timeLog['start_time']);
        $endTimeStamp = strtotime($endTime);
        $durationMinutes = round(($endTimeStamp - $startTime) / 60);
        
        // Calculate amount
        $amount = 0;
        if ($timeLog['hourly_rate'] && $timeLog['is_billable']) {
            $amount = ($durationMinutes / 60) * $timeLog['hourly_rate'];
        }
        
        $this->timeLogModel->update($id, [
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'amount' => $amount
        ]);
        
        // Update task actual hours
        if ($timeLog['task_id']) {
            $taskModel = $this->model('Task');
            $taskModel->updateActualHours($timeLog['task_id']);
        }
        
        $updatedTimeLog = $this->timeLogModel->find($id);
        
        return $this->success('Timer stopped', $updatedTimeLog);
    }
    
    /**
     * Add manual time entry
     */
    public function manual() {
        $this->requireAuth();
        
        $data = [
            'user_id' => Auth::id(),
            'project_id' => $this->input('project_id'),
            'task_id' => $this->input('task_id'),
            'description' => $this->input('description'),
            'start_time' => $this->input('start_time'),
            'end_time' => $this->input('end_time'),
            'is_billable' => $this->input('is_billable', 1),
            'is_manual' => 1,
            'manual_justification' => $this->input('manual_justification')
        ];
        
        $validation = $this->validate($data, [
            'project_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'manual_justification' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Calculate duration
        $startTime = strtotime($data['start_time']);
        $endTime = strtotime($data['end_time']);
        $durationMinutes = round(($endTime - $startTime) / 60);
        
        if ($durationMinutes <= 0) {
            return $this->error('End time must be after start time', 400);
        }
        
        // Get hourly rate
        $hourlyRate = $this->timeLogModel->getUserHourlyRate(Auth::id(), $data['project_id']);
        $data['hourly_rate'] = $hourlyRate;
        $data['duration_minutes'] = $durationMinutes;
        
        // Calculate amount
        $amount = 0;
        if ($hourlyRate && $data['is_billable']) {
            $amount = ($durationMinutes / 60) * $hourlyRate;
        }
        $data['amount'] = $amount;
        
        $timeLogId = $this->timeLogModel->create($data);
        
        // Update task actual hours
        if ($data['task_id']) {
            $taskModel = $this->model('Task');
            $taskModel->updateActualHours($data['task_id']);
        }
        
        $timeLog = $this->timeLogModel->find($timeLogId);
        
        return $this->success('Manual time entry added', $timeLog, 201);
    }
    
    /**
     * Delete time log
     */
    public function delete($id) {
        $this->requireAuth();
        
        $timeLog = $this->timeLogModel->find($id);
        
        if (!$timeLog) {
            return $this->error('Time log not found', 404);
        }
        
        // Check permission
        if (!$this->hasRole([ROLE_ADMIN, ROLE_PROJECT_MANAGER]) && $timeLog['user_id'] != Auth::id()) {
            return $this->error('Forbidden', 403);
        }
        
        $this->timeLogModel->delete($id);
        
        // Update task actual hours
        if ($timeLog['task_id']) {
            $taskModel = $this->model('Task');
            $taskModel->updateActualHours($timeLog['task_id']);
        }
        
        return $this->success('Time log deleted');
    }
}

