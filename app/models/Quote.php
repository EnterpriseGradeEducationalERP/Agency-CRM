<?php
/**
 * Quote Model
 * Handles quote data operations
 */

class Quote extends Model {
    protected $table = 'quotes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'quote_number',
        'client_id',
        'deal_id',
        'title',
        'subtotal',
        'overhead_percentage',
        'overhead_amount',
        'margin_percentage',
        'margin_amount',
        'tax_percentage',
        'tax_amount',
        'total',
        'offer_multiplier',
        'offer_value',
        'currency',
        'valid_until',
        'status',
        'version',
        'notes',
        'terms',
        'created_by'
    ];
    protected $timestamps = true;
    
    /**
     * Get quote items
     */
    public function getItems($quoteId) {
        $sql = "SELECT qi.*, s.name as service_name, jr.name as role_name 
                FROM quote_items qi 
                LEFT JOIN services s ON qi.service_id = s.id 
                LEFT JOIN job_roles jr ON qi.role_id = jr.id 
                WHERE qi.quote_id = :quote_id 
                ORDER BY qi.order_position ASC";
        
        return $this->db->fetchAll($sql, ['quote_id' => $quoteId]);
    }
    
    /**
     * Add item to quote
     */
    public function addItem($quoteId, $data) {
        $data['quote_id'] = $quoteId;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('quote_items', $data);
    }
    
    /**
     * Get client for quote
     */
    public function getClient($quoteId) {
        $sql = "SELECT c.* FROM clients c 
                INNER JOIN quotes q ON c.id = q.client_id 
                WHERE q.id = :quote_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['quote_id' => $quoteId]);
    }
    
    /**
     * Get quotes by status
     */
    public function getByStatus($status) {
        return $this->all(['status' => $status], 'created_at DESC');
    }
}

