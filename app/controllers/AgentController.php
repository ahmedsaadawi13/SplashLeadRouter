<?php
// FILE: /app/controllers/AgentController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TenantSubscription.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/CSRF.php';

/**
 * AgentController
 * Manages agents
 */
class AgentController extends Controller {

    /**
     * List agents
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $userModel = $this->model('User');
        $agents = $userModel->getAgentsByTenant($tenantId, false);

        // Get capacity info
        $agentsWithCapacity = $userModel->getAgentsCapacity($tenantId);

        $data = [
            'agents' => $agents,
            'agents_capacity' => $agentsWithCapacity
        ];

        $this->view('agents/index', $data);
    }

    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        $tenantId = $this->getCurrentTenantId();

        if ($subscriptionModel->hasExceededLimit($tenantId, 'agents')) {
            View::setFlash('error', 'You have reached your subscription limit for agents');
            $this->redirect('/agents');
        }

        $this->view('agents/create');
    }

    /**
     * Store new agent
     */
    public function store() {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasExceededLimit($tenantId, 'agents')) {
            View::setFlash('error', 'You have reached your subscription limit for agents');
            $this->redirect('/agents');
        }

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('email')->email('email')
                  ->required('password')->min('password', 6)
                  ->required('phone')->phone('phone')
                  ->numeric('max_active_leads');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/agents/create');
        }

        $userModel = $this->model('User');

        // Check if email exists
        if ($userModel->findByEmail($_POST['email'])) {
            View::setFlash('error', 'Email already exists');
            View::setOld($_POST);
            $this->redirect('/agents/create');
        }

        $agentData = [
            'tenant_id' => $tenantId,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'phone' => $_POST['phone'],
            'role' => 'agent',
            'agent_level' => $_POST['agent_level'] ?? 'junior',
            'max_active_leads' => $_POST['max_active_leads'] ?? 10,
            'is_active' => 1
        ];

        $userModel->createUser($agentData);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Agent created successfully');
        $this->redirect('/agents');
    }

    /**
     * Show agent details
     */
    public function show($id) {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $userModel = $this->model('User');
        $agent = $userModel->findById($id);

        if (!$agent || ($agent['tenant_id'] != $tenantId && $this->getCurrentUserRole() !== 'platform_admin')) {
            View::setFlash('error', 'Agent not found');
            $this->redirect('/agents');
        }

        // Get metrics
        $agentWithMetrics = $userModel->getAgentWithMetrics($id);

        // Get recent leads
        $leadModel = $this->model('Lead');
        $recentLeads = $leadModel->getLeadsByAgent($id, [], 10, 0);

        $data = [
            'agent' => $agentWithMetrics,
            'recent_leads' => $recentLeads
        ];

        $this->view('agents/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getCurrentTenantId();

        $userModel = $this->model('User');
        $agent = $userModel->findById($id);

        if (!$agent || $agent['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Agent not found');
            $this->redirect('/agents');
        }

        $data = ['agent' => $agent];
        $this->view('agents/edit', $data);
    }

    /**
     * Update agent
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $userModel = $this->model('User');

        $agent = $userModel->findById($id);
        if (!$agent || $agent['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Agent not found');
            $this->redirect('/agents');
        }

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('phone')->phone('phone')
                  ->numeric('max_active_leads');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/agents/' . $id . '/edit');
        }

        $agentData = [
            'name' => $_POST['name'],
            'phone' => $_POST['phone'],
            'agent_level' => $_POST['agent_level'] ?? 'junior',
            'max_active_leads' => $_POST['max_active_leads'] ?? 10,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Update password if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                View::setFlash('error', 'Password must be at least 6 characters');
                $this->redirect('/agents/' . $id . '/edit');
            }
            $userModel->updatePassword($id, $_POST['password']);
        }

        $userModel->update($id, $agentData);

        View::setFlash('success', 'Agent updated successfully');
        $this->redirect('/agents/' . $id);
    }

    /**
     * Delete agent
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $userModel = $this->model('User');

        $agent = $userModel->findById($id);
        if (!$agent || $agent['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Agent not found');
            $this->redirect('/agents');
        }

        $userModel->delete($id);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Agent deleted successfully');
        $this->redirect('/agents');
    }
}
