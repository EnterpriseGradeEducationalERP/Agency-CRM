<?php
/**
 * Invoice Model
 * Handles invoice data operations
 */

class Invoice extends Model {
    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_number',
        'client_id',
        'project_id',
        'quote_id',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total',
        'paid_amount',
        'balance',
        'currency',
        'status',
        'notes',
        'terms',
        'created_by'
    ];
    protected $timestamps = true;
    
    /**
     * Get invoice items
     */
    public function getItems($invoiceId) {
        $sql = "SELECT * FROM invoice_items 
                WHERE invoice_id = :invoice_id 
                ORDER BY order_position ASC";
        
        return $this->db->fetchAll($sql, ['invoice_id' => $invoiceId]);
    }
    
    /**
     * Add item to invoice
     */
    public function addItem($invoiceId, $data) {
        $data['invoice_id'] = $invoiceId;
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('invoice_items', $data);
    }
    
    /**
     * Get client for invoice
     */
    public function getClient($invoiceId) {
        $sql = "SELECT c.* FROM clients c 
                INNER JOIN invoices i ON c.id = i.client_id 
                WHERE i.id = :invoice_id LIMIT 1";
        
        return $this->db->fetchOne($sql, ['invoice_id' => $invoiceId]);
    }
    
    /**
     * Get payments for invoice
     */
    public function getPayments($invoiceId) {
        $sql = "SELECT * FROM payments 
                WHERE invoice_id = :invoice_id 
                ORDER BY payment_date DESC";
        
        return $this->db->fetchAll($sql, ['invoice_id' => $invoiceId]);
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($invoiceId) {
        $invoice = $this->find($invoiceId);
        
        if (!$invoice) {
            return false;
        }
        
        $status = INVOICE_SENT;
        
        if ($invoice['paid_amount'] >= $invoice['total']) {
            $status = INVOICE_PAID;
        } elseif ($invoice['paid_amount'] > 0) {
            $status = INVOICE_PARTIALLY_PAID;
        } elseif (strtotime($invoice['due_date']) < time()) {
            $status = INVOICE_OVERDUE;
        }
        
        return $this->update($invoiceId, ['status' => $status]);
    }
    
    /**
     * Add payment
     */
    public function addPayment($invoiceId, $amount) {
        $invoice = $this->find($invoiceId);
        
        if (!$invoice) {
            return false;
        }
        
        $paidAmount = $invoice['paid_amount'] + $amount;
        $balance = $invoice['total'] - $paidAmount;
        
        $this->update($invoiceId, [
            'paid_amount' => $paidAmount,
            'balance' => $balance
        ]);
        
        $this->updatePaymentStatus($invoiceId);
        
        return true;
    }
    
    /**
     * Get overdue invoices
     */
    public function getOverdue() {
        $sql = "SELECT * FROM invoices 
                WHERE status NOT IN ('paid', 'void') 
                AND due_date < CURDATE() 
                ORDER BY due_date ASC";
        
        return $this->db->fetchAll($sql);
    }
}

