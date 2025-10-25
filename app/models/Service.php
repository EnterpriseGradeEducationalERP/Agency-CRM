<?php
/**
 * Service Model
 * Handles service data operations
 */

class Service extends Model {
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'is_active'
    ];
    protected $timestamps = true;
    
    /**
     * Get roles associated with service
     */
    public function getRoles($serviceId) {
        $sql = "SELECT jr.*, srm.default_hours 
                FROM job_roles jr 
                INNER JOIN service_role_map srm ON jr.id = srm.role_id 
                WHERE srm.service_id = :service_id 
                ORDER BY jr.name ASC";
        
        return $this->db->fetchAll($sql, ['service_id' => $serviceId]);
    }
    
    /**
     * Sync roles for service
     */
    public function syncRoles($serviceId, $roles) {
        // Delete existing mappings
        $this->db->delete('service_role_map', 'service_id = :service_id', ['service_id' => $serviceId]);
        
        // Add new mappings
        foreach ($roles as $role) {
            $this->db->insert('service_role_map', [
                'service_id' => $serviceId,
                'role_id' => $role['role_id'],
                'default_hours' => $role['default_hours'] ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Get active services
     */
    public function getActive() {
        return $this->all(['is_active' => 1], 'name ASC');
    }
}

