<?php
/**
 * Payment Model
 * Handles payment data operations
 */

class Payment extends Model {
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id',
        'client_id',
        'payment_method',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_date',
        'notes',
        'gateway_response'
    ];
    protected $timestamps = true;
    
    /**
     * Get invoice for payment
     */
    public function getInvoice($paymentId) {
        $sql = "SELECT i.* FROM invoices i 
                INNER JOIN payments p ON i.id = p.invoice_id 
                WHERE p.id = :payment_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['payment_id' => $paymentId]);
    }
    
    /**
     * Get payments by invoice
     */
    public function getByInvoice($invoiceId) {
        return $this->all(['invoice_id' => $invoiceId], 'payment_date DESC');
    }
    
    /**
     * Get successful payments
     */
    public function getSuccessful($startDate = null, $endDate = null) {
        $sql = "SELECT * FROM payments WHERE status = :status";
        $params = ['status' => PAYMENT_SUCCESS];
        
        if ($startDate) {
            $sql .= " AND payment_date >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND payment_date <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY payment_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}

