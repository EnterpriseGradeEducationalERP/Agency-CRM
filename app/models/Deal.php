<?php
/**
 * Deal Model
 * Handles deal/pipeline data operations
 */

class Deal extends Model {
    protected $table = 'deals';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'client_id',
        'stage_id',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'assigned_to',
        'next_followup',
        'description',
        'notes',
        'status',
        'lost_reason'
    ];
    protected $timestamps = true;
    
    /**
     * Get deals by stage
     */
    public function getByStage($stageId) {
        $sql = "SELECT d.*, c.company_name, c.contact_person, 
                u.first_name, u.last_name 
                FROM deals d 
                LEFT JOIN clients c ON d.client_id = c.id 
                LEFT JOIN users u ON d.assigned_to = u.id 
                WHERE d.stage_id = :stage_id AND d.status = 'open'
                ORDER BY d.created_at DESC";
        
        return $this->db->fetchAll($sql, ['stage_id' => $stageId]);
    }
    
    /**
     * Get total value by stage
     */
    public function getTotalValueByStage($stageId) {
        $sql = "SELECT SUM(value) as total 
                FROM deals 
                WHERE stage_id = :stage_id AND status = 'open'";
        
        $result = $this->db->fetchOne($sql, ['stage_id' => $stageId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get client for deal
     */
    public function getClient($dealId) {
        $sql = "SELECT c.* FROM clients c 
                INNER JOIN deals d ON c.id = d.client_id 
                WHERE d.id = :deal_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['deal_id' => $dealId]);
    }
    
    /**
     * Get stage for deal
     */
    public function getStage($dealId) {
        $sql = "SELECT ps.* FROM pipeline_stages ps 
                INNER JOIN deals d ON ps.id = d.stage_id 
                WHERE d.id = :deal_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['deal_id' => $dealId]);
    }
    
    /**
     * Get activities for deal (placeholder)
     */
    public function getActivities($dealId) {
        // TODO: Implement activity tracking
        return [];
    }
    
    /**
     * Get open deals
     */
    public function getOpen() {
        return $this->all(['status' => 'open'], 'created_at DESC');
    }
}

