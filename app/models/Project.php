<?php
/**
 * Project Model
 * Handles project data operations
 */

class Project extends Model {
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'code',
        'client_id',
        'deal_id',
        'quote_id',
        'description',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'status',
        'progress',
        'project_manager_id',
        'priority',
        'notes'
    ];
    protected $timestamps = true;
    
    /**
     * Get client for project
     */
    public function getClient($projectId) {
        $sql = "SELECT c.* FROM clients c 
                INNER JOIN projects p ON c.id = p.client_id 
                WHERE p.id = :project_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get manager for project
     */
    public function getManager($projectId) {
        $sql = "SELECT u.id, u.email, u.first_name, u.last_name, u.phone 
                FROM users u 
                INNER JOIN projects p ON u.id = p.project_manager_id 
                WHERE p.id = :project_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get team members for project
     */
    public function getTeam($projectId) {
        $sql = "SELECT pt.*, u.email, u.first_name, u.last_name, u.role as user_role 
                FROM project_team pt 
                INNER JOIN users u ON pt.user_id = u.id 
                WHERE pt.project_id = :project_id AND pt.left_at IS NULL 
                ORDER BY pt.joined_at DESC";
        
        return $this->db->fetchAll($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get team count
     */
    public function getTeamCount($projectId) {
        $sql = "SELECT COUNT(*) as count FROM project_team 
                WHERE project_id = :project_id AND left_at IS NULL";
        
        $result = $this->db->fetchOne($sql, ['project_id' => $projectId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Add team member
     */
    public function addTeamMember($projectId, $data) {
        $data['project_id'] = $projectId;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('project_team', $data);
    }
    
    /**
     * Remove team member
     */
    public function removeTeamMember($projectId, $userId) {
        return $this->db->update(
            'project_team',
            ['left_at' => date('Y-m-d')],
            'project_id = :project_id AND user_id = :user_id',
            ['project_id' => $projectId, 'user_id' => $userId]
        );
    }
    
    /**
     * Get task stats
     */
    public function getTaskStats($projectId) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
                FROM tasks 
                WHERE project_id = :project_id";
        
        return $this->db->fetchOne($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get time tracking stats
     */
    public function getTimeStats($projectId) {
        $sql = "SELECT 
                SUM(duration_minutes) as total_minutes,
                SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END) as billable_minutes,
                SUM(amount) as total_amount
                FROM time_logs 
                WHERE project_id = :project_id";
        
        $result = $this->db->fetchOne($sql, ['project_id' => $projectId]);
        
        return [
            'total_hours' => round(($result['total_minutes'] ?? 0) / 60, 2),
            'billable_hours' => round(($result['billable_minutes'] ?? 0) / 60, 2),
            'total_amount' => $result['total_amount'] ?? 0
        ];
    }
    
    /**
     * Get active projects
     */
    public function getActive() {
        return $this->all(['status' => PROJECT_ACTIVE], 'start_date DESC');
    }
}

