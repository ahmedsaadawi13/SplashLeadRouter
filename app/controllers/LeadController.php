<?php
// FILE: /app/controllers/LeadController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/LeadNote.php';
require_once __DIR__ . '/../models/LeadSource.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/CSRF.php';
require_once __DIR__ . '/../helpers/RoutingEngine.php';

/**
 * LeadController
 * Manages leads
 */
class LeadController extends Controller {

    /**
     * List leads
     */
    public function index() {
        $this->requireAuth();
        $tenantId = $this->getCurrentTenantId();
        $role = $this->getCurrentUserRole();

        $leadModel = $this->model('Lead');
        $userModel = $this->model('User');

        // Build filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['source'])) {
            $filters['source'] = $_GET['source'];
        }
        if (!empty($_GET['agent'])) {
            $filters['assigned_agent_id'] = $_GET['agent'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Get leads based on role
        if ($role === 'agent') {
            $leads = $leadModel->getLeadsByAgent($this->getCurrentUserId(), $filters, $perPage, $offset);
            $totalLeads = $leadModel->count(['assigned_agent_id' => $this->getCurrentUserId()]);
        } else {
            $leads = $leadModel->getLeadsByTenant($tenantId, $filters, $perPage, $offset);
            $totalLeads = $leadModel->countByTenant($tenantId, $filters);
        }

        $totalPages = ceil($totalLeads / $perPage);

        // Get agents for filter
        $agents = $userModel->getAgentsByTenant($tenantId);

        $data = [
            'leads' => $leads,
            'agents' => $agents,
            'filters' => $filters,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_leads' => $totalLeads
        ];

        $this->view('leads/index', $data);
    }

    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['tenant_admin', 'agent']);
        $tenantId = $this->getCurrentTenantId();

        $sourceModel = $this->model('LeadSource');
        $sources = $sourceModel->getSourcesByTenant($tenantId);

        $data = ['sources' => $sources];
        $this->view('leads/create', $data);
    }

    /**
     * Store new lead
     */
    public function store() {
        $this->requireRole(['tenant_admin', 'agent']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('phone')->phone('phone')
                  ->email('email');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/leads/create');
        }

        $leadModel = $this->model('Lead');

        $leadData = [
            'tenant_id' => $tenantId,
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'] ?? null,
            'source' => $_POST['source'] ?? 'manual',
            'campaign' => $_POST['campaign'] ?? null,
            'property_type' => $_POST['property_type'] ?? null,
            'budget_min' => !empty($_POST['budget_min']) ? $_POST['budget_min'] : null,
            'budget_max' => !empty($_POST['budget_max']) ? $_POST['budget_max'] : null,
            'preferred_city' => $_POST['preferred_city'] ?? null,
            'preferred_area' => $_POST['preferred_area'] ?? null,
            'notes' => $_POST['notes'] ?? null,
            'status' => 'new'
        ];

        $leadId = $leadModel->create($leadData);

        // Auto-route the lead
        $routingEngine = new RoutingEngine();
        $routingEngine->routeLead($leadId, $tenantId);

        View::setFlash('success', 'Lead created and routed successfully');
        $this->redirect('/leads/' . $leadId);
    }

    /**
     * Show lead details
     */
    public function show($id) {
        $this->requireAuth();
        $tenantId = $this->getCurrentTenantId();

        $leadModel = $this->model('Lead');
        $noteModel = $this->model('LeadNote');
        $logModel = $this->model('RoutingLog');

        $lead = $leadModel->findById($id);

        if (!$lead || ($lead['tenant_id'] != $tenantId && $this->getCurrentUserRole() !== 'platform_admin')) {
            View::setFlash('error', 'Lead not found');
            $this->redirect('/leads');
        }

        // Get notes
        $notes = $noteModel->getNotesByLead($id);

        // Get routing logs
        $logs = $logModel->getLogsByLead($id);

        $data = [
            'lead' => $lead,
            'notes' => $notes,
            'logs' => $logs
        ];

        $this->view('leads/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin', 'agent']);
        $tenantId = $this->getCurrentTenantId();

        $leadModel = $this->model('Lead');
        $lead = $leadModel->findById($id);

        if (!$lead || $lead['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Lead not found');
            $this->redirect('/leads');
        }

        $sourceModel = $this->model('LeadSource');
        $sources = $sourceModel->getSourcesByTenant($tenantId);

        $data = [
            'lead' => $lead,
            'sources' => $sources
        ];

        $this->view('leads/edit', $data);
    }

    /**
     * Update lead
     */
    public function update($id) {
        $this->requireRole(['tenant_admin', 'agent']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $leadModel = $this->model('Lead');

        $lead = $leadModel->findById($id);
        if (!$lead || $lead['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Lead not found');
            $this->redirect('/leads');
        }

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('phone')->phone('phone');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/leads/' . $id . '/edit');
        }

        $leadData = [
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'] ?? null,
            'source' => $_POST['source'] ?? 'manual',
            'campaign' => $_POST['campaign'] ?? null,
            'property_type' => $_POST['property_type'] ?? null,
            'budget_min' => !empty($_POST['budget_min']) ? $_POST['budget_min'] : null,
            'budget_max' => !empty($_POST['budget_max']) ? $_POST['budget_max'] : null,
            'preferred_city' => $_POST['preferred_city'] ?? null,
            'preferred_area' => $_POST['preferred_area'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];

        $leadModel->update($id, $leadData);

        View::setFlash('success', 'Lead updated successfully');
        $this->redirect('/leads/' . $id);
    }

    /**
     * Update lead status
     */
    public function updateStatus($id) {
        $this->requireAuth();
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $leadModel = $this->model('Lead');

        $lead = $leadModel->findById($id);
        if (!$lead || $lead['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'Lead not found'], 404);
        }

        $status = $_POST['status'] ?? '';
        $validStatuses = ['new', 'assigned', 'contacted', 'qualified', 'unqualified', 'closed_won', 'closed_lost'];

        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $leadModel->updateStatus($id, $status);

        $this->json(['success' => true, 'message' => 'Status updated']);
    }

    /**
     * Add note to lead
     */
    public function addNote($id) {
        $this->requireAuth();
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $leadModel = $this->model('Lead');

        $lead = $leadModel->findById($id);
        if (!$lead || $lead['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Lead not found');
            $this->redirect('/leads');
        }

        $note = $_POST['note'] ?? '';
        if (empty($note)) {
            View::setFlash('error', 'Note cannot be empty');
            $this->redirect('/leads/' . $id);
        }

        $noteModel = $this->model('LeadNote');
        $noteModel->addNote($id, $this->getCurrentUserId(), $note);

        View::setFlash('success', 'Note added');
        $this->redirect('/leads/' . $id);
    }

    /**
     * Reassign lead
     */
    public function reassign($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $leadModel = $this->model('Lead');

        $lead = $leadModel->findById($id);
        if (!$lead || $lead['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Lead not found');
            $this->redirect('/leads');
        }

        $newAgentId = $_POST['agent_id'] ?? 0;

        $routingEngine = new RoutingEngine();
        $result = $routingEngine->reassignLead($id, $newAgentId, $this->getCurrentUserId());

        if ($result) {
            View::setFlash('success', 'Lead reassigned successfully');
        } else {
            View::setFlash('error', 'Failed to reassign lead');
        }

        $this->redirect('/leads/' . $id);
    }
}
