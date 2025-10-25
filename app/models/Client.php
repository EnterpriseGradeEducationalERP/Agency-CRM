<?php
/**
 * Client Model
 * Handles client data operations
 */

class Client extends Model {
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'gstin',
        'source',
        'status',
        'assigned_to',
        'notes'
    ];
    protected $timestamps = true;
    
    /**
     * Get client by email
     */
    public function findByEmail($email) {
        return $this->findWhere(['email' => $email]);
    }
    
    /**
     * Get deals for client
     */
    public function getDeals($clientId) {
        $sql = "SELECT d.*, ps.name as stage_name, u.first_name, u.last_name 
                FROM deals d 
                LEFT JOIN pipeline_stages ps ON d.stage_id = ps.id 
                LEFT JOIN users u ON d.assigned_to = u.id 
                WHERE d.client_id = :client_id 
                ORDER BY d.created_at DESC";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }
    
    /**
     * Get projects for client
     */
    public function getProjects($clientId) {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM projects p 
                LEFT JOIN users u ON p.project_manager_id = u.id 
                WHERE p.client_id = :client_id 
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }
    
    /**
     * Get quotes for client
     */
    public function getQuotes($clientId) {
        $sql = "SELECT q.*, u.first_name, u.last_name 
                FROM quotes q 
                LEFT JOIN users u ON q.created_by = u.id 
                WHERE q.client_id = :client_id 
                ORDER BY q.created_at DESC";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }
    
    /**
     * Get invoices for client
     */
    public function getInvoices($clientId) {
        $sql = "SELECT i.*, u.first_name, u.last_name 
                FROM invoices i 
                LEFT JOIN users u ON i.created_by = u.id 
                WHERE i.client_id = :client_id 
                ORDER BY i.created_at DESC";
        
        return $this->db->fetchAll($sql, ['client_id' => $clientId]);
    }
    
    /**
     * Get active clients
     */
    public function getActive() {
        return $this->all(['status' => 'active'], 'company_name ASC');
    }
}

