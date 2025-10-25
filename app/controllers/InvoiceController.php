<?php
/**
 * Invoice Controller
 * Handles invoice management operations
 */

class InvoiceController extends Controller {
    private $invoiceModel;
    private $settingsModel;
    
    public function __construct() {
        parent::__construct();
        $this->invoiceModel = $this->model('Invoice');
        $this->settingsModel = $this->model('Setting');
    }
    
    /**
     * Get all invoices
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER, ROLE_PROJECT_MANAGER]);
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $status = $this->input('status');
        $clientId = $this->input('client_id');
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($clientId) {
            $conditions['client_id'] = $clientId;
        }
        
        $result = $this->invoiceModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Invoices retrieved', $result);
    }
    
    /**
     * Get single invoice
     */
    public function show($id) {
        $this->requireAuth();
        
        $invoice = $this->invoiceModel->find($id);
        
        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }
        
        $invoice['items'] = $this->invoiceModel->getItems($id);
        $invoice['client'] = $this->invoiceModel->getClient($id);
        $invoice['payments'] = $this->invoiceModel->getPayments($id);
        
        return $this->success('Invoice retrieved', $invoice);
    }
    
    /**
     * Create new invoice
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER, ROLE_PROJECT_MANAGER]);
        
        $invoiceNumber = $this->generateInvoiceNumber();
        
        $data = [
            'invoice_number' => $invoiceNumber,
            'client_id' => $this->input('client_id'),
            'project_id' => $this->input('project_id'),
            'quote_id' => $this->input('quote_id'),
            'issue_date' => $this->input('issue_date', date('Y-m-d')),
            'due_date' => $this->input('due_date'),
            'subtotal' => $this->input('subtotal', 0),
            'tax_percentage' => $this->input('tax_percentage', DEFAULT_GST_PERCENTAGE),
            'tax_amount' => $this->input('tax_amount', 0),
            'discount_percentage' => $this->input('discount_percentage', 0),
            'discount_amount' => $this->input('discount_amount', 0),
            'total' => $this->input('total', 0),
            'paid_amount' => 0,
            'balance' => $this->input('total', 0),
            'currency' => $this->input('currency', 'INR'),
            'status' => INVOICE_DRAFT,
            'notes' => $this->input('notes'),
            'terms' => $this->input('terms'),
            'created_by' => Auth::id()
        ];
        
        $validation = $this->validate($data, [
            'client_id' => 'required',
            'due_date' => 'required',
            'total' => 'required|numeric'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $invoiceId = $this->invoiceModel->create($data);
        
        // Add items
        $items = $this->input('items', []);
        foreach ($items as $index => $item) {
            $this->invoiceModel->addItem($invoiceId, [
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'order_position' => $index + 1
            ]);
        }
        
        $invoice = $this->invoiceModel->find($invoiceId);
        $invoice['items'] = $this->invoiceModel->getItems($invoiceId);
        
        return $this->success('Invoice created', $invoice, 201);
    }
    
    /**
     * Update invoice
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER]);
        
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }
        
        $data = [
            'issue_date' => $this->input('issue_date', $invoice['issue_date']),
            'due_date' => $this->input('due_date', $invoice['due_date']),
            'subtotal' => $this->input('subtotal', $invoice['subtotal']),
            'tax_percentage' => $this->input('tax_percentage', $invoice['tax_percentage']),
            'tax_amount' => $this->input('tax_amount', $invoice['tax_amount']),
            'discount_percentage' => $this->input('discount_percentage', $invoice['discount_percentage']),
            'discount_amount' => $this->input('discount_amount', $invoice['discount_amount']),
            'total' => $this->input('total', $invoice['total']),
            'status' => $this->input('status', $invoice['status']),
            'notes' => $this->input('notes', $invoice['notes']),
            'terms' => $this->input('terms', $invoice['terms'])
        ];
        
        // Recalculate balance
        $data['balance'] = $data['total'] - $invoice['paid_amount'];
        
        $this->invoiceModel->update($id, $data);
        
        // Update items if provided
        $items = $this->input('items');
        if ($items !== null) {
            // Delete existing items
            $this->db->delete('invoice_items', 'invoice_id = :invoice_id', ['invoice_id' => $id]);
            
            // Add new items
            foreach ($items as $index => $item) {
                $this->invoiceModel->addItem($id, [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['amount'],
                    'order_position' => $index + 1
                ]);
            }
        }
        
        $updatedInvoice = $this->invoiceModel->find($id);
        $updatedInvoice['items'] = $this->invoiceModel->getItems($id);
        
        return $this->success('Invoice updated', $updatedInvoice);
    }
    
    /**
     * Delete invoice
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }
        
        // Check if invoice has payments
        if ($invoice['paid_amount'] > 0) {
            return $this->error('Cannot delete invoice with payments. Please void it instead.', 400);
        }
        
        $this->invoiceModel->delete($id);
        return $this->success('Invoice deleted');
    }
    
    /**
     * Generate PDF
     */
    public function generatePdf($id) {
        $this->requireAuth();
        
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }
        
        // TODO: Implement PDF generation with TCPDF
        return $this->success('PDF generation coming soon', ['invoice_id' => $id]);
    }
    
    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber() {
        $prefix = $this->settingsModel->getValue('invoice_prefix', 'INV');
        $year = date('Y');
        $month = date('m');
        
        // Get last invoice number for this month
        $sql = "SELECT invoice_number FROM invoices 
                WHERE invoice_number LIKE :pattern 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $lastInvoice = $this->db->fetchOne($sql, ['pattern' => $pattern]);
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice['invoice_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $newNumber);
    }
}

