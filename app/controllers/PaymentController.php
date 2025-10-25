<?php
/**
 * Payment Controller
 * Handles payment processing (Razorpay, Stripe)
 */

class PaymentController extends Controller {
    private $paymentModel;
    private $invoiceModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = $this->model('Payment');
        $this->invoiceModel = $this->model('Invoice');
    }
    
    /**
     * Get all payments
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER]);
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $status = $this->input('status');
        $clientId = $this->input('client_id');
        $invoiceId = $this->input('invoice_id');
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($clientId) {
            $conditions['client_id'] = $clientId;
        }
        if ($invoiceId) {
            $conditions['invoice_id'] = $invoiceId;
        }
        
        $result = $this->paymentModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Payments retrieved', $result);
    }
    
    /**
     * Get single payment
     */
    public function show($id) {
        $this->requireAuth();
        
        $payment = $this->paymentModel->find($id);
        
        if (!$payment) {
            return $this->error('Payment not found', 404);
        }
        
        $payment['invoice'] = $this->paymentModel->getInvoice($id);
        
        return $this->success('Payment retrieved', $payment);
    }
    
    /**
     * Create new payment / initiate payment
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_FINANCE_OFFICER]);
        
        $invoiceId = $this->input('invoice_id');
        $amount = $this->input('amount');
        $paymentMethod = $this->input('payment_method');
        
        $validation = $this->validate($_POST, [
            'invoice_id' => 'required',
            'amount' => 'required|numeric',
            'payment_method' => 'required'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            return $this->error('Invoice not found', 404);
        }
        
        // For manual payment methods (bank transfer, cash, cheque)
        if (in_array($paymentMethod, [PAYMENT_BANK_TRANSFER, PAYMENT_CASH, PAYMENT_CHEQUE])) {
            $data = [
                'invoice_id' => $invoiceId,
                'client_id' => $invoice['client_id'],
                'payment_method' => $paymentMethod,
                'transaction_id' => $this->input('transaction_id'),
                'amount' => $amount,
                'currency' => $invoice['currency'],
                'status' => PAYMENT_SUCCESS,
                'payment_date' => date('Y-m-d H:i:s'),
                'notes' => $this->input('notes')
            ];
            
            $paymentId = $this->paymentModel->create($data);
            
            // Update invoice
            $this->invoiceModel->addPayment($invoiceId, $amount);
            
            $payment = $this->paymentModel->find($paymentId);
            return $this->success('Payment recorded', $payment, 201);
        }
        
        // For online payment gateways
        if ($paymentMethod === PAYMENT_RAZORPAY) {
            return $this->initiateRazorpay($invoice, $amount);
        }
        
        if ($paymentMethod === PAYMENT_STRIPE) {
            return $this->initiateStripe($invoice, $amount);
        }
        
        return $this->error('Invalid payment method', 400);
    }
    
    /**
     * Initiate Razorpay payment
     */
    private function initiateRazorpay($invoice, $amount) {
        // TODO: Integrate Razorpay SDK
        // For now, return mock response
        
        $data = [
            'invoice_id' => $invoice['id'],
            'client_id' => $invoice['client_id'],
            'payment_method' => PAYMENT_RAZORPAY,
            'amount' => $amount,
            'currency' => $invoice['currency'],
            'status' => PAYMENT_PENDING
        ];
        
        $paymentId = $this->paymentModel->create($data);
        
        return $this->success('Razorpay payment initiated', [
            'payment_id' => $paymentId,
            'razorpay_order_id' => 'order_' . uniqid(),
            'amount' => $amount * 100, // Razorpay expects paise
            'currency' => $invoice['currency'],
            'key' => getenv('RAZORPAY_KEY_ID')
        ]);
    }
    
    /**
     * Initiate Stripe payment
     */
    private function initiateStripe($invoice, $amount) {
        // TODO: Integrate Stripe SDK
        // For now, return mock response
        
        $data = [
            'invoice_id' => $invoice['id'],
            'client_id' => $invoice['client_id'],
            'payment_method' => PAYMENT_STRIPE,
            'amount' => $amount,
            'currency' => $invoice['currency'],
            'status' => PAYMENT_PENDING
        ];
        
        $paymentId = $this->paymentModel->create($data);
        
        return $this->success('Stripe payment initiated', [
            'payment_id' => $paymentId,
            'client_secret' => 'pi_' . uniqid() . '_secret',
            'amount' => $amount * 100, // Stripe expects cents
            'currency' => strtolower($invoice['currency']),
            'public_key' => getenv('STRIPE_PUBLIC_KEY')
        ]);
    }
    
    /**
     * Razorpay webhook callback
     */
    public function razorpayCallback() {
        // TODO: Verify Razorpay signature
        $data = $this->input();
        
        $paymentId = $data['payment_id'] ?? null;
        $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
        $status = $data['status'] ?? PAYMENT_SUCCESS;
        
        if ($paymentId && $razorpayPaymentId) {
            $payment = $this->paymentModel->find($paymentId);
            
            if ($payment) {
                $this->paymentModel->update($paymentId, [
                    'transaction_id' => $razorpayPaymentId,
                    'status' => $status,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'gateway_response' => json_encode($data)
                ]);
                
                if ($status === PAYMENT_SUCCESS) {
                    $this->invoiceModel->addPayment($payment['invoice_id'], $payment['amount']);
                }
            }
        }
        
        return $this->success('Payment callback processed');
    }
    
    /**
     * Stripe webhook callback
     */
    public function stripeCallback() {
        // TODO: Verify Stripe signature
        $data = $this->input();
        
        $paymentId = $data['payment_id'] ?? null;
        $stripePaymentId = $data['stripe_payment_id'] ?? null;
        $status = $data['status'] ?? PAYMENT_SUCCESS;
        
        if ($paymentId && $stripePaymentId) {
            $payment = $this->paymentModel->find($paymentId);
            
            if ($payment) {
                $this->paymentModel->update($paymentId, [
                    'transaction_id' => $stripePaymentId,
                    'status' => $status,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'gateway_response' => json_encode($data)
                ]);
                
                if ($status === PAYMENT_SUCCESS) {
                    $this->invoiceModel->addPayment($payment['invoice_id'], $payment['amount']);
                }
            }
        }
        
        return $this->success('Payment callback processed');
    }
}

