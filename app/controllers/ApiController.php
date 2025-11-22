<?php
// FILE: /app/controllers/ApiController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ApiKey.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/TenantSubscription.php';
require_once __DIR__ . '/../helpers/RoutingEngine.php';

/**
 * ApiController
 * Handles REST API endpoints for external integrations
 */
class ApiController extends Controller {

    private $tenant = null;

    /**
     * Authenticate API request
     * @return bool
     */
    private function authenticateApi() {
        $apiKey = null;

        // Check for API key in header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        }
        // Fallback to GET parameter (less secure, but for testing)
        elseif (isset($_GET['api_key'])) {
            $apiKey = $_GET['api_key'];
        }

        if (!$apiKey) {
            $this->json([
                'success' => false,
                'message' => 'API key required'
            ], 401);
            return false;
        }

        $apiKeyModel = $this->model('ApiKey');
        $this->tenant = $apiKeyModel->verifyKey($apiKey);

        if (!$this->tenant) {
            $this->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 401);
            return false;
        }

        return true;
    }

    /**
     * Create new lead via API
     * POST /api/leads
     */
    public function createLead() {
        if (!$this->authenticateApi()) {
            return;
        }

        // Parse JSON body
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            $this->json([
                'success' => false,
                'message' => 'Invalid JSON'
            ], 400);
            return;
        }

        // Validate required fields
        if (empty($data['name']) || empty($data['phone'])) {
            $this->json([
                'success' => false,
                'message' => 'Name and phone are required',
                'errors' => [
                    'name' => empty($data['name']) ? 'Name is required' : null,
                    'phone' => empty($data['phone']) ? 'Phone is required' : null
                ]
            ], 400);
            return;
        }

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasExceededLimit($this->tenant['tenant_id'], 'leads')) {
            $this->json([
                'success' => false,
                'message' => 'Monthly lead limit exceeded for your subscription plan'
            ], 429);
            return;
        }

        // Create lead
        $leadModel = $this->model('Lead');

        $leadData = [
            'tenant_id' => $this->tenant['tenant_id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'source' => $data['source'] ?? 'api',
            'campaign' => $data['campaign'] ?? null,
            'property_type' => $data['property_type'] ?? null,
            'budget_min' => isset($data['budget_min']) ? (float)$data['budget_min'] : null,
            'budget_max' => isset($data['budget_max']) ? (float)$data['budget_max'] : null,
            'preferred_city' => $data['city'] ?? $data['preferred_city'] ?? null,
            'preferred_area' => $data['area'] ?? $data['preferred_area'] ?? null,
            'preferred_location' => $data['preferred_location'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'new'
        ];

        try {
            $leadId = $leadModel->create($leadData);

            // Auto-route the lead
            $routingEngine = new RoutingEngine();
            $routingResult = $routingEngine->routeLead($leadId, $this->tenant['tenant_id']);

            // Update usage tracking
            require_once __DIR__ . '/../models/UsageTracking.php';
            $usageModel = new UsageTracking();
            $usageModel->updateUsage($this->tenant['tenant_id']);

            // Get created lead
            $lead = $leadModel->findById($leadId);

            $response = [
                'success' => true,
                'message' => 'Lead created and routed successfully',
                'data' => [
                    'lead_id' => $leadId,
                    'status' => $lead['status'],
                    'assigned_agent_id' => $lead['assigned_agent_id'],
                    'assignment_status' => $routingResult['success'] ? 'assigned' : 'unassigned',
                    'agent_name' => $routingResult['agent_name'] ?? null,
                    'rule_used' => $routingResult['rule_name'] ?? null,
                    'routing_reason' => $routingResult['reason'] ?? null
                ]
            ];

            $this->json($response, 201);

        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Failed to create lead',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lead status via API
     * PATCH/PUT /api/leads/:id
     */
    public function updateLead($id) {
        if (!$this->authenticateApi()) {
            return;
        }

        $leadModel = $this->model('Lead');
        $lead = $leadModel->findById($id);

        if (!$lead || $lead['tenant_id'] != $this->tenant['tenant_id']) {
            $this->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
            return;
        }

        // Parse JSON body
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            $this->json([
                'success' => false,
                'message' => 'Invalid JSON'
            ], 400);
            return;
        }

        // Update status if provided
        if (isset($data['status'])) {
            $validStatuses = ['new', 'assigned', 'contacted', 'qualified', 'unqualified', 'closed_won', 'closed_lost'];

            if (!in_array($data['status'], $validStatuses)) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid status',
                    'valid_statuses' => $validStatuses
                ], 400);
                return;
            }

            $leadModel->updateStatus($id, $data['status']);
        }

        // Add note if provided
        if (!empty($data['notes'])) {
            require_once __DIR__ . '/../models/LeadNote.php';
            $noteModel = new LeadNote();

            // Use a system user ID or the first admin
            $systemUserId = 1; // This should be configured properly

            $noteModel->addNote($id, $systemUserId, $data['notes']);
        }

        // Get updated lead
        $updatedLead = $leadModel->findById($id);

        $this->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => [
                'lead_id' => $updatedLead['id'],
                'status' => $updatedLead['status'],
                'assigned_agent_id' => $updatedLead['assigned_agent_id'],
                'updated_at' => $updatedLead['updated_at']
            ]
        ], 200);
    }

    /**
     * Get lead details via API
     * GET /api/leads/:id
     */
    public function getLead($id) {
        if (!$this->authenticateApi()) {
            return;
        }

        $leadModel = $this->model('Lead');
        $lead = $leadModel->findById($id);

        if (!$lead || $lead['tenant_id'] != $this->tenant['tenant_id']) {
            $this->json([
                'success' => false,
                'message' => 'Lead not found'
            ], 404);
            return;
        }

        $this->json([
            'success' => true,
            'data' => $lead
        ], 200);
    }

    /**
     * API documentation endpoint
     * GET /api/docs
     */
    public function docs() {
        $docs = [
            'version' => '1.0',
            'base_url' => '/api',
            'authentication' => 'API Key via X-API-KEY header',
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/leads',
                    'description' => 'Create a new lead',
                    'authentication' => 'Required',
                    'request_body' => [
                        'name' => 'string (required)',
                        'phone' => 'string (required)',
                        'email' => 'string (optional)',
                        'source' => 'string (optional)',
                        'campaign' => 'string (optional)',
                        'property_type' => 'string (optional)',
                        'budget_min' => 'number (optional)',
                        'budget_max' => 'number (optional)',
                        'city' => 'string (optional)',
                        'area' => 'string (optional)',
                        'notes' => 'string (optional)'
                    ],
                    'response' => [
                        'success' => 'boolean',
                        'message' => 'string',
                        'data' => [
                            'lead_id' => 'integer',
                            'assigned_agent_id' => 'integer',
                            'assignment_status' => 'string'
                        ]
                    ]
                ],
                [
                    'method' => 'PATCH',
                    'path' => '/api/leads/:id',
                    'description' => 'Update lead status',
                    'authentication' => 'Required',
                    'request_body' => [
                        'status' => 'string (new, contacted, qualified, closed_won, etc.)',
                        'notes' => 'string (optional)'
                    ],
                    'response' => [
                        'success' => 'boolean',
                        'message' => 'string',
                        'data' => [
                            'lead_id' => 'integer',
                            'status' => 'string'
                        ]
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/leads/:id',
                    'description' => 'Get lead details',
                    'authentication' => 'Required',
                    'response' => [
                        'success' => 'boolean',
                        'data' => 'object (lead details)'
                    ]
                ]
            ]
        ];

        $this->json($docs, 200);
    }
}
