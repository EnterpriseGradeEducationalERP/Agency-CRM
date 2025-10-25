<?php
/**
 * Time Log Model
 * Handles time tracking data operations
 */

class TimeLog extends Model {
    protected $table = 'time_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_billable',
        'hourly_rate',
        'amount',
        'is_manual',
        'manual_justification'
    ];
    protected $timestamps = true;
    
    /**
     * Get active timer for user
     */
    public function getActiveTimer($userId) {
        $sql = "SELECT * FROM time_logs 
                WHERE user_id = :user_id AND end_time IS NULL 
                LIMIT 1";
        
        return $this->db->fetchOne($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get user hourly rate from project team
     */
    public function getUserHourlyRate($userId, $projectId) {
        $sql = "SELECT hourly_rate FROM project_team 
                WHERE user_id = :user_id AND project_id = :project_id 
                LIMIT 1";
        
        $result = $this->db->fetchOne($sql, [
            'user_id' => $userId,
            'project_id' => $projectId
        ]);
        
        // If not found, get from job role
        if (!$result || !$result['hourly_rate']) {
            $sql = "SELECT jr.hourly_rate 
                    FROM users u 
                    LEFT JOIN job_roles jr ON u.role = jr.slug 
                    WHERE u.id = :user_id 
                    LIMIT 1";
            
            $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        }
        
        return $result['hourly_rate'] ?? 0;
    }
    
    /**
     * Get time logs by project
     */
    public function getByProject($projectId) {
        $sql = "SELECT tl.*, u.first_name, u.last_name, t.title as task_title 
                FROM time_logs tl 
                INNER JOIN users u ON tl.user_id = u.id 
                LEFT JOIN tasks t ON tl.task_id = t.id 
                WHERE tl.project_id = :project_id 
                ORDER BY tl.start_time DESC";
        
        return $this->db->fetchAll($sql, ['project_id' => $projectId]);
    }
    
    /**
     * Get time logs by user
     */
    public function getByUser($userId, $startDate = null, $endDate = null) {
        $sql = "SELECT tl.*, p.name as project_name, t.title as task_title 
                FROM time_logs tl 
                LEFT JOIN projects p ON tl.project_id = p.id 
                LEFT JOIN tasks t ON tl.task_id = t.id 
                WHERE tl.user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if ($startDate) {
            $sql .= " AND tl.start_time >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND tl.start_time <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY tl.start_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total hours by user
     */
    public function getTotalHoursByUser($userId, $projectId = null) {
        $sql = "SELECT 
                SUM(duration_minutes) as total_minutes,
                SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END) as billable_minutes
                FROM time_logs 
                WHERE user_id = :user_id";
        
        $params = ['user_id' => $userId];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        
        return [
            'total_hours' => round(($result['total_minutes'] ?? 0) / 60, 2),
            'billable_hours' => round(($result['billable_minutes'] ?? 0) / 60, 2)
        ];
    }
}

