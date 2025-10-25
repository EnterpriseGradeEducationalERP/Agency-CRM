<?php
/**
 * Task Controller
 * Handles task management operations
 */

class TaskController extends Controller {
    private $taskModel;
    
    public function __construct() {
        parent::__construct();
        $this->taskModel = $this->model('Task');
    }
    
    /**
     * Get all tasks (filtered)
     */
    public function index() {
        $this->requireAuth();
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 50);
        $projectId = $this->input('project_id');
        $assignedTo = $this->input('assigned_to');
        $status = $this->input('status');
        
        $conditions = [];
        if ($projectId) {
            $conditions['project_id'] = $projectId;
        }
        if ($assignedTo) {
            $conditions['assigned_to'] = $assignedTo;
        }
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $result = $this->taskModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Tasks retrieved', $result);
    }
    
    /**
     * Get tasks for a project (Kanban view)
     */
    public function projectTasks($projectId) {
        $this->requireAuth();
        
        $tasks = $this->taskModel->getByProject($projectId);
        
        // Group by status for Kanban
        $kanban = [
            'todo' => [],
            'in_progress' => [],
            'blocked' => [],
            'done' => []
        ];
        
        foreach ($tasks as $task) {
            $kanban[$task['status']][] = $task;
        }
        
        return $this->success('Tasks retrieved', $kanban);
    }
    
    /**
     * Get single task
     */
    public function show($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        
        if (!$task) {
            return $this->error('Task not found', 404);
        }
        
        $task['comments'] = $this->taskModel->getComments($id);
        $task['time_logs'] = $this->taskModel->getTimeLogs($id);
        
        return $this->success('Task retrieved', $task);
    }
    
    /**
     * Create new task
     */
    public function create() {
        $this->requireAuth();
        
        $data = [
            'project_id' => $this->input('project_id'),
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'status' => $this->input('status', TASK_TODO),
            'priority' => $this->input('priority', PRIORITY_MEDIUM),
            'assigned_to' => $this->input('assigned_to'),
            'estimated_hours' => $this->input('estimated_hours'),
            'start_date' => $this->input('start_date'),
            'due_date' => $this->input('due_date'),
            'parent_task_id' => $this->input('parent_task_id'),
            'tags' => $this->input('tags'),
            'created_by' => Auth::id()
        ];
        
        $validation = $this->validate($data, [
            'project_id' => 'required',
            'title' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $taskId = $this->taskModel->create($data);
        $task = $this->taskModel->find($taskId);
        
        return $this->success('Task created', $task, 201);
    }
    
    /**
     * Update task
     */
    public function update($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        if (!$task) {
            return $this->error('Task not found', 404);
        }
        
        $data = [
            'title' => $this->input('title', $task['title']),
            'description' => $this->input('description', $task['description']),
            'status' => $this->input('status', $task['status']),
            'priority' => $this->input('priority', $task['priority']),
            'assigned_to' => $this->input('assigned_to', $task['assigned_to']),
            'estimated_hours' => $this->input('estimated_hours', $task['estimated_hours']),
            'start_date' => $this->input('start_date', $task['start_date']),
            'due_date' => $this->input('due_date', $task['due_date']),
            'tags' => $this->input('tags', $task['tags'])
        ];
        
        // Mark as completed if status changed to done
        if ($data['status'] === TASK_DONE && $task['status'] !== TASK_DONE) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        $this->taskModel->update($id, $data);
        $updatedTask = $this->taskModel->find($id);
        
        return $this->success('Task updated', $updatedTask);
    }
    
    /**
     * Delete task
     */
    public function delete($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        if (!$task) {
            return $this->error('Task not found', 404);
        }
        
        $this->taskModel->delete($id);
        return $this->success('Task deleted');
    }
    
    /**
     * Add comment to task
     */
    public function addComment($id) {
        $this->requireAuth();
        
        $task = $this->taskModel->find($id);
        if (!$task) {
            return $this->error('Task not found', 404);
        }
        
        $comment = $this->input('comment');
        if (empty($comment)) {
            return $this->error('Comment is required', 400);
        }
        
        $this->taskModel->addComment($id, Auth::id(), $comment);
        $comments = $this->taskModel->getComments($id);
        
        return $this->success('Comment added', $comments);
    }
}

