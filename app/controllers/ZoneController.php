<?php
// FILE: /app/controllers/ZoneController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Zone.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TenantSubscription.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/CSRF.php';

/**
 * ZoneController
 * Manages zones and territories
 */
class ZoneController extends Controller {

    /**
     * List zones
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $zoneModel = $this->model('Zone');
        $zones = $zoneModel->getZonesByTenant($tenantId, false);

        $data = ['zones' => $zones];
        $this->view('zones/index', $data);
    }

    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        $tenantId = $this->getCurrentTenantId();

        if ($subscriptionModel->hasExceededLimit($tenantId, 'zones')) {
            View::setFlash('error', 'You have reached your subscription limit for zones');
            $this->redirect('/zones');
        }

        $this->view('zones/create');
    }

    /**
     * Store new zone
     */
    public function store() {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasExceededLimit($tenantId, 'zones')) {
            View::setFlash('error', 'You have reached your subscription limit for zones');
            $this->redirect('/zones');
        }

        $validator = new Validator($_POST);
        $validator->required('name');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/zones/create');
        }

        $zoneModel = $this->model('Zone');

        $zoneData = [
            'tenant_id' => $tenantId,
            'name' => $_POST['name'],
            'country' => $_POST['country'] ?? null,
            'region' => $_POST['region'] ?? null,
            'city' => $_POST['city'] ?? null,
            'subarea' => $_POST['subarea'] ?? null,
            'custom_label' => $_POST['custom_label'] ?? null,
            'is_active' => 1
        ];

        $zoneId = $zoneModel->create($zoneData);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Zone created successfully');
        $this->redirect('/zones/' . $zoneId);
    }

    /**
     * Show zone details with agents
     */
    public function show($id) {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $zoneModel = $this->model('Zone');
        $zone = $zoneModel->getZoneWithAgents($id);

        if (!$zone || ($zone['tenant_id'] != $tenantId && $this->getCurrentUserRole() !== 'platform_admin')) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        // Get available agents (not yet assigned to this zone)
        $userModel = $this->model('User');
        $allAgents = $userModel->getAgentsByTenant($tenantId);

        $assignedIds = array_column($zone['agents'], 'id');
        $availableAgents = array_filter($allAgents, function($agent) use ($assignedIds) {
            return !in_array($agent['id'], $assignedIds);
        });

        $data = [
            'zone' => $zone,
            'available_agents' => $availableAgents
        ];

        $this->view('zones/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getCurrentTenantId();

        $zoneModel = $this->model('Zone');
        $zone = $zoneModel->findById($id);

        if (!$zone || $zone['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        $data = ['zone' => $zone];
        $this->view('zones/edit', $data);
    }

    /**
     * Update zone
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $zoneModel = $this->model('Zone');

        $zone = $zoneModel->findById($id);
        if (!$zone || $zone['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        $validator = new Validator($_POST);
        $validator->required('name');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/zones/' . $id . '/edit');
        }

        $zoneData = [
            'name' => $_POST['name'],
            'country' => $_POST['country'] ?? null,
            'region' => $_POST['region'] ?? null,
            'city' => $_POST['city'] ?? null,
            'subarea' => $_POST['subarea'] ?? null,
            'custom_label' => $_POST['custom_label'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $zoneModel->update($id, $zoneData);

        View::setFlash('success', 'Zone updated successfully');
        $this->redirect('/zones/' . $id);
    }

    /**
     * Assign agent to zone
     */
    public function assignAgent($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $zoneModel = $this->model('Zone');

        $zone = $zoneModel->findById($id);
        if (!$zone || $zone['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        $agentId = $_POST['agent_id'] ?? 0;
        $priority = $_POST['priority'] ?? 1;
        $weight = $_POST['weight'] ?? 1;
        $isPrimary = isset($_POST['is_primary']) ? 1 : 0;

        $zoneModel->assignAgent($id, $agentId, $priority, $weight, $isPrimary);

        View::setFlash('success', 'Agent assigned to zone');
        $this->redirect('/zones/' . $id);
    }

    /**
     * Remove agent from zone
     */
    public function removeAgent($id, $agentId) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $zoneModel = $this->model('Zone');

        $zone = $zoneModel->findById($id);
        if (!$zone || $zone['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        $zoneModel->removeAgent($id, $agentId);

        View::setFlash('success', 'Agent removed from zone');
        $this->redirect('/zones/' . $id);
    }

    /**
     * Delete zone
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $zoneModel = $this->model('Zone');

        $zone = $zoneModel->findById($id);
        if (!$zone || $zone['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Zone not found');
            $this->redirect('/zones');
        }

        $zoneModel->delete($id);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Zone deleted successfully');
        $this->redirect('/zones');
    }
}
