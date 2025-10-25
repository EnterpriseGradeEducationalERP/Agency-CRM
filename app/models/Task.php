<?php
/**
 * Task Model
 * Handles task data operations
 */

class Task extends Model {
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'estimated_hours',
        'actual_hours',
        'start_date',
        'due_date',
        'completed_at',
        'parent_task_id',
        'order_position',
        'tags',
        'created_by'
    ];
    protected $timestamps = true;
    
    /**
     * Get tasks by project
     */
    public function getByProject($projectId) {
        $sql = "SELECT t.*, 
                u.first_name, u.last_name,
                creator.first_name as creator_first_name, creator.last_name as creator_last_name
                FROM tasks t 
                LEFT JOIN users u ON t.assigned_to = u.id 
                LEFT JOIN users creator ON t.created_by = creator.id 
                WHERE t.project_id = :project_id 
                ORDER BY t.order_position ASC, t.created_at DESC";
        
        return $this->db->fetchAll($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get comments for task
     */
    public function getComments($taskId) {
        $sql = "SELECT tc.*, u.first_name, u.last_name, u.avatar 
                FROM task_comments tc 
                INNER JOIN users u ON tc.user_id = u.id 
                WHERE tc.task_id = :task_id 
                ORDER BY tc.created_at DESC";
        
        return $this->db->fetchAll($sql, ['task_id' => $taskId]);
    }
    
    /**
     * Add comment
     */
    public function addComment($taskId, $userId, $comment) {
        return $this->db->insert('task_comments', [
            'task_id' => $taskId,
            'user_id' => $userId,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get time logs for task
     */
    public function getTimeLogs($taskId) {
        $sql = "SELECT tl.*, u.first_name, u.last_name 
                FROM time_logs tl 
                INNER JOIN users u ON tl.user_id = u.id 
                WHERE tl.task_id = :task_id 
                ORDER BY tl.start_time DESC";
        
        return $this->db->fetchAll($sql, ['task_id' => $taskId]);
    }
    
    /**
     * Update actual hours from time logs
     */
    public function updateActualHours($taskId) {
        $sql = "SELECT SUM(duration_minutes) as total_minutes 
                FROM time_logs 
                WHERE task_id = :task_id";
        
        $result = $this->db->fetchOne($sql, ['task_id' => $taskId]);
        $actualHours = round(($result['total_minutes'] ?? 0) / 60, 2);
        
        $this->update($taskId, ['actual_hours' => $actualHours]);
        return $actualHours;
    }
    
    /**
     * Get tasks assigned to user
     */
    public function getAssignedTo($userId) {
        return $this->all(['assigned_to' => $userId], 'due_date ASC');
    }
}

