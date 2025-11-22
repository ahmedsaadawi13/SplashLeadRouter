<?php
// FILE: /app/controllers/RoutingRuleController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/RoutingRule.php';
require_once __DIR__ . '/../models/TenantSubscription.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/CSRF.php';

/**
 * RoutingRuleController
 * Manages routing rules
 */
class RoutingRuleController extends Controller {

    /**
     * List routing rules
     */
    public function index() {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $ruleModel = $this->model('RoutingRule');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $rules = $ruleModel->getRulesByTenant($tenantId, $perPage, $offset);
        $totalRules = $ruleModel->count(['tenant_id' => $tenantId]);
        $totalPages = ceil($totalRules / $perPage);

        $data = [
            'rules' => $rules,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];

        $this->view('routing/index', $data);
    }

    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['tenant_admin']);

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        $tenantId = $this->getCurrentTenantId();

        if ($subscriptionModel->hasExceededLimit($tenantId, 'routing_rules')) {
            View::setFlash('error', 'You have reached your subscription limit for routing rules');
            $this->redirect('/routing-rules');
        }

        $this->view('routing/create');
    }

    /**
     * Store new rule
     */
    public function store() {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();

        // Check subscription limits
        $subscriptionModel = $this->model('TenantSubscription');
        if ($subscriptionModel->hasExceededLimit($tenantId, 'routing_rules')) {
            View::setFlash('error', 'You have reached your subscription limit for routing rules');
            $this->redirect('/routing-rules');
        }

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('rule_type')
                  ->numeric('priority');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/routing-rules/create');
        }

        // Build conditions JSON
        $conditions = [];
        if ($_POST['rule_type'] === 'budget_based') {
            if (!empty($_POST['budget_min'])) {
                $conditions['budget_min'] = (float)$_POST['budget_min'];
            }
            if (!empty($_POST['budget_max'])) {
                $conditions['budget_max'] = (float)$_POST['budget_max'];
            }
        } elseif ($_POST['rule_type'] === 'property_type_based') {
            if (!empty($_POST['property_type'])) {
                $conditions['property_type'] = $_POST['property_type'];
            }
        } elseif ($_POST['rule_type'] === 'source_based') {
            if (!empty($_POST['source'])) {
                $conditions['source'] = $_POST['source'];
            }
        } elseif ($_POST['rule_type'] === 'zone_based') {
            if (!empty($_POST['zones'])) {
                $conditions['zones'] = explode(',', $_POST['zones']);
            }
        }

        $ruleModel = $this->model('RoutingRule');

        $ruleData = [
            'tenant_id' => $tenantId,
            'name' => $_POST['name'],
            'rule_type' => $_POST['rule_type'],
            'priority' => $_POST['priority'] ?? 1,
            'conditions' => json_encode($conditions),
            'assignment_strategy' => $_POST['assignment_strategy'] ?? 'round_robin',
            'target_agent_ids' => !empty($_POST['target_agent_ids']) ? json_encode(explode(',', $_POST['target_agent_ids'])) : null,
            'is_active' => 1
        ];

        $ruleModel->create($ruleData);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Routing rule created successfully');
        $this->redirect('/routing-rules');
    }

    /**
     * Show rule details
     */
    public function show($id) {
        $this->requireRole(['tenant_admin', 'platform_admin']);
        $tenantId = $this->getCurrentTenantId();

        $ruleModel = $this->model('RoutingRule');
        $rule = $ruleModel->findById($id);

        if (!$rule || ($rule['tenant_id'] != $tenantId && $this->getCurrentUserRole() !== 'platform_admin')) {
            View::setFlash('error', 'Rule not found');
            $this->redirect('/routing-rules');
        }

        $data = ['rule' => $rule];
        $this->view('routing/show', $data);
    }

    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['tenant_admin']);
        $tenantId = $this->getCurrentTenantId();

        $ruleModel = $this->model('RoutingRule');
        $rule = $ruleModel->findById($id);

        if (!$rule || $rule['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Rule not found');
            $this->redirect('/routing-rules');
        }

        $data = ['rule' => $rule];
        $this->view('routing/edit', $data);
    }

    /**
     * Update rule
     */
    public function update($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $ruleModel = $this->model('RoutingRule');

        $rule = $ruleModel->findById($id);
        if (!$rule || $rule['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Rule not found');
            $this->redirect('/routing-rules');
        }

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->numeric('priority');

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/routing-rules/' . $id . '/edit');
        }

        // Build conditions JSON
        $conditions = [];
        if ($rule['rule_type'] === 'budget_based') {
            if (!empty($_POST['budget_min'])) {
                $conditions['budget_min'] = (float)$_POST['budget_min'];
            }
            if (!empty($_POST['budget_max'])) {
                $conditions['budget_max'] = (float)$_POST['budget_max'];
            }
        } elseif ($rule['rule_type'] === 'property_type_based') {
            if (!empty($_POST['property_type'])) {
                $conditions['property_type'] = $_POST['property_type'];
            }
        } elseif ($rule['rule_type'] === 'source_based') {
            if (!empty($_POST['source'])) {
                $conditions['source'] = $_POST['source'];
            }
        }

        $ruleData = [
            'name' => $_POST['name'],
            'priority' => $_POST['priority'] ?? 1,
            'conditions' => json_encode($conditions),
            'assignment_strategy' => $_POST['assignment_strategy'] ?? 'round_robin',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $ruleModel->update($id, $ruleData);

        View::setFlash('success', 'Routing rule updated successfully');
        $this->redirect('/routing-rules/' . $id);
    }

    /**
     * Toggle rule status
     */
    public function toggleStatus($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $ruleModel = $this->model('RoutingRule');

        $rule = $ruleModel->findById($id);
        if (!$rule || $rule['tenant_id'] != $tenantId) {
            $this->json(['success' => false, 'message' => 'Rule not found'], 404);
        }

        $ruleModel->toggleStatus($id);

        $this->json(['success' => true, 'message' => 'Status updated']);
    }

    /**
     * Delete rule
     */
    public function delete($id) {
        $this->requireRole(['tenant_admin']);
        CSRF::requireToken();

        $tenantId = $this->getCurrentTenantId();
        $ruleModel = $this->model('RoutingRule');

        $rule = $ruleModel->findById($id);
        if (!$rule || $rule['tenant_id'] != $tenantId) {
            View::setFlash('error', 'Rule not found');
            $this->redirect('/routing-rules');
        }

        $ruleModel->delete($id);

        // Update usage tracking
        require_once __DIR__ . '/../models/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usageModel->updateUsage($tenantId);

        View::setFlash('success', 'Routing rule deleted successfully');
        $this->redirect('/routing-rules');
    }
}
