<?php
/**
 * Job Role Model
 * Handles job role data operations
 */

class JobRole extends Model {
    protected $table = 'job_roles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'hourly_rate',
        'currency',
        'description',
        'is_active'
    ];
    protected $timestamps = true;
    
    /**
     * Get active roles
     */
    public function getActive() {
        return $this->all(['is_active' => 1], 'name ASC');
    }
    
    /**
     * Get services for role
     */
    public function getServices($roleId) {
        $sql = "SELECT s.*, srm.default_hours 
                FROM services s 
                INNER JOIN service_role_map srm ON s.id = srm.service_id 
                WHERE srm.role_id = :role_id 
                ORDER BY s.name ASC";
        
        return $this->db->fetchAll($sql, ['role_id' => $roleId]);
    }
}

