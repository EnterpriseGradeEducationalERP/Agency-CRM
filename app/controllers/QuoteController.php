<?php
/**
 * Quote Controller
 * Handles quote management and pricing calculator
 */

class QuoteController extends Controller {
    private $quoteModel;
    private $settingsModel;
    
    public function __construct() {
        parent::__construct();
        $this->quoteModel = $this->model('Quote');
        $this->settingsModel = $this->model('Setting');
    }
    
    /**
     * Calculate quote/pricing
     */
    public function calculate() {
        $this->requireAuth();
        
        $items = $this->input('items', []);
        $overheadPercentage = $this->input('overhead_percentage', DEFAULT_OVERHEAD_PERCENTAGE);
        $marginPercentage = $this->input('margin_percentage', DEFAULT_MARGIN_PERCENTAGE);
        $taxPercentage = $this->input('tax_percentage', DEFAULT_GST_PERCENTAGE);
        $offerMultiplier = $this->input('offer_multiplier', DEFAULT_OFFER_MULTIPLIER);
        $currency = $this->input('currency', 'INR');
        
        if (empty($items)) {
            return $this->error('Items are required', 400);
        }
        
        // Calculate subtotal
        $subtotal = 0;
        $calculatedItems = [];
        
        foreach ($items as $item) {
            $hours = $item['hours'] ?? 0;
            $rate = $item['hourly_rate'] ?? 0;
            $amount = $hours * $rate;
            
            $calculatedItems[] = [
                'description' => $item['description'],
                'hours' => $hours,
                'hourly_rate' => $rate,
                'amount' => $amount,
                'service_id' => $item['service_id'] ?? null,
                'role_id' => $item['role_id'] ?? null
            ];
            
            $subtotal += $amount;
        }
        
        // Calculate overhead
        $overheadAmount = ($subtotal * $overheadPercentage) / 100;
        
        // Calculate base cost (subtotal + overhead)
        $baseCost = $subtotal + $overheadAmount;
        
        // Calculate margin
        $marginAmount = ($baseCost * $marginPercentage) / 100;
        
        // Calculate pre-tax total
        $preTaxTotal = $baseCost + $marginAmount;
        
        // Calculate tax
        $taxAmount = ($preTaxTotal * $taxPercentage) / 100;
        
        // Calculate final total
        $total = $preTaxTotal + $taxAmount;
        
        // Calculate offer value (200% value proposition)
        $offerValue = $total * $offerMultiplier;
        
        $result = [
            'items' => $calculatedItems,
            'subtotal' => round($subtotal, 2),
            'overhead_percentage' => $overheadPercentage,
            'overhead_amount' => round($overheadAmount, 2),
            'base_cost' => round($baseCost, 2),
            'margin_percentage' => $marginPercentage,
            'margin_amount' => round($marginAmount, 2),
            'pre_tax_total' => round($preTaxTotal, 2),
            'tax_percentage' => $taxPercentage,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'offer_multiplier' => $offerMultiplier,
            'offer_value' => round($offerValue, 2),
            'currency' => $currency
        ];
        
        return $this->success('Quote calculated', $result);
    }
    
    /**
     * Get all quotes
     */
    public function index() {
        $this->requireAuth();
        
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
        
        $result = $this->quoteModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Quotes retrieved', $result);
    }
    
    /**
     * Get single quote
     */
    public function show($id) {
        $this->requireAuth();
        
        $quote = $this->quoteModel->find($id);
        
        if (!$quote) {
            return $this->error('Quote not found', 404);
        }
        
        $quote['items'] = $this->quoteModel->getItems($id);
        $quote['client'] = $this->quoteModel->getClient($id);
        
        return $this->success('Quote retrieved', $quote);
    }
    
    /**
     * Create new quote
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $quoteNumber = $this->generateQuoteNumber();
        
        $data = [
            'quote_number' => $quoteNumber,
            'client_id' => $this->input('client_id'),
            'deal_id' => $this->input('deal_id'),
            'title' => $this->input('title'),
            'subtotal' => $this->input('subtotal', 0),
            'overhead_percentage' => $this->input('overhead_percentage', DEFAULT_OVERHEAD_PERCENTAGE),
            'overhead_amount' => $this->input('overhead_amount', 0),
            'margin_percentage' => $this->input('margin_percentage', DEFAULT_MARGIN_PERCENTAGE),
            'margin_amount' => $this->input('margin_amount', 0),
            'tax_percentage' => $this->input('tax_percentage', DEFAULT_GST_PERCENTAGE),
            'tax_amount' => $this->input('tax_amount', 0),
            'total' => $this->input('total', 0),
            'offer_multiplier' => $this->input('offer_multiplier', DEFAULT_OFFER_MULTIPLIER),
            'offer_value' => $this->input('offer_value', 0),
            'currency' => $this->input('currency', 'INR'),
            'valid_until' => $this->input('valid_until'),
            'status' => $this->input('status', QUOTE_DRAFT),
            'version' => 1,
            'notes' => $this->input('notes'),
            'terms' => $this->input('terms'),
            'created_by' => Auth::id()
        ];
        
        $validation = $this->validate($data, [
            'client_id' => 'required',
            'title' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $quoteId = $this->quoteModel->create($data);
        
        // Add items
        $items = $this->input('items', []);
        foreach ($items as $index => $item) {
            $this->quoteModel->addItem($quoteId, [
                'service_id' => $item['service_id'] ?? null,
                'role_id' => $item['role_id'] ?? null,
                'description' => $item['description'],
                'hours' => $item['hours'],
                'hourly_rate' => $item['hourly_rate'],
                'amount' => $item['amount'],
                'order_position' => $index + 1
            ]);
        }
        
        $quote = $this->quoteModel->find($quoteId);
        $quote['items'] = $this->quoteModel->getItems($quoteId);
        
        return $this->success('Quote created', $quote, 201);
    }
    
    /**
     * Update quote
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            return $this->error('Quote not found', 404);
        }
        
        $data = [
            'title' => $this->input('title', $quote['title']),
            'subtotal' => $this->input('subtotal', $quote['subtotal']),
            'overhead_percentage' => $this->input('overhead_percentage', $quote['overhead_percentage']),
            'overhead_amount' => $this->input('overhead_amount', $quote['overhead_amount']),
            'margin_percentage' => $this->input('margin_percentage', $quote['margin_percentage']),
            'margin_amount' => $this->input('margin_amount', $quote['margin_amount']),
            'tax_percentage' => $this->input('tax_percentage', $quote['tax_percentage']),
            'tax_amount' => $this->input('tax_amount', $quote['tax_amount']),
            'total' => $this->input('total', $quote['total']),
            'offer_multiplier' => $this->input('offer_multiplier', $quote['offer_multiplier']),
            'offer_value' => $this->input('offer_value', $quote['offer_value']),
            'currency' => $this->input('currency', $quote['currency']),
            'valid_until' => $this->input('valid_until', $quote['valid_until']),
            'status' => $this->input('status', $quote['status']),
            'notes' => $this->input('notes', $quote['notes']),
            'terms' => $this->input('terms', $quote['terms'])
        ];
        
        $this->quoteModel->update($id, $data);
        
        // Update items if provided
        $items = $this->input('items');
        if ($items !== null) {
            // Delete existing items
            $this->db->delete('quote_items', 'quote_id = :quote_id', ['quote_id' => $id]);
            
            // Add new items
            foreach ($items as $index => $item) {
                $this->quoteModel->addItem($id, [
                    'service_id' => $item['service_id'] ?? null,
                    'role_id' => $item['role_id'] ?? null,
                    'description' => $item['description'],
                    'hours' => $item['hours'],
                    'hourly_rate' => $item['hourly_rate'],
                    'amount' => $item['amount'],
                    'order_position' => $index + 1
                ]);
            }
        }
        
        $updatedQuote = $this->quoteModel->find($id);
        $updatedQuote['items'] = $this->quoteModel->getItems($id);
        
        return $this->success('Quote updated', $updatedQuote);
    }
    
    /**
     * Delete quote
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            return $this->error('Quote not found', 404);
        }
        
        $this->quoteModel->delete($id);
        return $this->success('Quote deleted');
    }
    
    /**
     * Generate PDF
     */
    public function generatePdf($id) {
        $this->requireAuth();
        
        $quote = $this->quoteModel->find($id);
        if (!$quote) {
            return $this->error('Quote not found', 404);
        }
        
        // TODO: Implement PDF generation with TCPDF
        return $this->success('PDF generation coming soon', ['quote_id' => $id]);
    }
    
    /**
     * Generate quote number
     */
    private function generateQuoteNumber() {
        $prefix = $this->settingsModel->getValue('quote_prefix', 'QOT');
        $year = date('Y');
        $month = date('m');
        
        // Get last quote number for this month
        $sql = "SELECT quote_number FROM quotes 
                WHERE quote_number LIKE :pattern 
                ORDER BY id DESC LIMIT 1";
        
        $pattern = "{$prefix}-{$year}{$month}-%";
        $lastQuote = $this->db->fetchOne($sql, ['pattern' => $pattern]);
        
        if ($lastQuote) {
            $lastNumber = (int) substr($lastQuote['quote_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $newNumber);
    }
}

