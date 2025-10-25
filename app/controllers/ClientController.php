<?php
/**
 * Client Controller
 * Handles client/CRM management operations
 */

class ClientController extends Controller {
    private $clientModel;
    
    public function __construct() {
        parent::__construct();
        $this->clientModel = $this->model('Client');
    }
    
    /**
     * Get all clients
     */
    public function index() {
        $this->requireAuth();
        
        $page = $this->input('page', 1);
        $perPage = $this->input('per_page', 20);
        $status = $this->input('status', 'active');
        $source = $this->input('source');
        $search = $this->input('search');
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        if ($source) {
            $conditions['source'] = $source;
        }
        
        $result = $this->clientModel->paginate($page, $perPage, $conditions, 'created_at DESC');
        
        return $this->success('Clients retrieved', $result);
    }
    
    /**
     * Get single client
     */
    public function show($id) {
        $this->requireAuth();
        
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            return $this->error('Client not found', 404);
        }
        
        // Get related data
        $client['deals'] = $this->clientModel->getDeals($id);
        $client['projects'] = $this->clientModel->getProjects($id);
        $client['quotes'] = $this->clientModel->getQuotes($id);
        $client['invoices'] = $this->clientModel->getInvoices($id);
        
        return $this->success('Client retrieved', $client);
    }
    
    /**
     * Create new client
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $data = [
            'company_name' => $this->input('company_name'),
            'contact_person' => $this->input('contact_person'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'website' => $this->input('website'),
            'address' => $this->input('address'),
            'city' => $this->input('city'),
            'state' => $this->input('state'),
            'country' => $this->input('country'),
            'postal_code' => $this->input('postal_code'),
            'gstin' => $this->input('gstin'),
            'source' => $this->input('source', 'lead'),
            'status' => $this->input('status', 'active'),
            'assigned_to' => $this->input('assigned_to', Auth::id()),
            'notes' => $this->input('notes')
        ];
        
        $validation = $this->validate($data, [
            'company_name' => 'required',
            'contact_person' => 'required',
            'email' => 'required|email'
        ]);
        
        if ($validation !== true) {
            return $this->error('Validation failed', 422, $validation);
        }
        
        // Check if email exists
        $existing = $this->clientModel->findWhere(['email' => $data['email']]);
        if ($existing) {
            return $this->error('Email already exists', 400);
        }
        
        $clientId = $this->clientModel->create($data);
        $client = $this->clientModel->find($clientId);
        
        return $this->success('Client created', $client, 201);
    }
    
    /**
     * Update client
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN, ROLE_SALES_EXECUTIVE, ROLE_PROJECT_MANAGER]);
        
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->error('Client not found', 404);
        }
        
        $data = [
            'company_name' => $this->input('company_name', $client['company_name']),
            'contact_person' => $this->input('contact_person', $client['contact_person']),
            'email' => $this->input('email', $client['email']),
            'phone' => $this->input('phone', $client['phone']),
            'website' => $this->input('website', $client['website']),
            'address' => $this->input('address', $client['address']),
            'city' => $this->input('city', $client['city']),
            'state' => $this->input('state', $client['state']),
            'country' => $this->input('country', $client['country']),
            'postal_code' => $this->input('postal_code', $client['postal_code']),
            'gstin' => $this->input('gstin', $client['gstin']),
            'source' => $this->input('source', $client['source']),
            'status' => $this->input('status', $client['status']),
            'assigned_to' => $this->input('assigned_to', $client['assigned_to']),
            'notes' => $this->input('notes', $client['notes'])
        ];
        
        $this->clientModel->update($id, $data);
        $updatedClient = $this->clientModel->find($id);
        
        return $this->success('Client updated', $updatedClient);
    }
    
    /**
     * Delete client
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole([ROLE_ADMIN]);
        
        $client = $this->clientModel->find($id);
        if (!$client) {
            return $this->error('Client not found', 404);
        }
        
        $this->clientModel->delete($id);
        return $this->success('Client deleted');
    }
}

