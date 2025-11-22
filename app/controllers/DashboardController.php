<?php
// FILE: /app/controllers/DashboardController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/TenantSubscription.php';
require_once __DIR__ . '/../models/UsageTracking.php';

/**
 * DashboardController
 * Main dashboard for all user roles
 */
class DashboardController extends Controller {

    /**
     * Show dashboard
     */
    public function index() {
        $this->requireAuth();

        $role = $this->getCurrentUserRole();

        if ($role === 'platform_admin') {
            $this->platformAdminDashboard();
        } elseif ($role === 'agent') {
            $this->agentDashboard();
        } else {
            $this->tenantAdminDashboard();
        }
    }

    /**
     * Platform admin dashboard
     */
    private function platformAdminDashboard() {
        $tenantModel = $this->model('Tenant');
        $leadModel = $this->model('Lead');

        // Get all tenants
        $tenants = $tenantModel->findAll([]);

        // Get global stats
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM leads");
        $totalLeads = $stmt->fetch()['count'];

        $stmt = $db->query("SELECT COUNT(*) as count FROM tenants WHERE status = 'active'");
        $activeTenants = $stmt->fetch()['count'];

        $data = [
            'tenants' => $tenants,
            'total_leads' => $totalLeads,
            'active_tenants' => $activeTenants
        ];

        $this->view('dashboard/platform_admin', $data);
    }

    /**
     * Tenant admin dashboard
     */
    private function tenantAdminDashboard() {
        $tenantId = $this->getCurrentTenantId();

        $leadModel = $this->model('Lead');
        $userModel = $this->model('User');
        $subscriptionModel = $this->model('TenantSubscription');
        $usageModel = $this->model('UsageTracking');

        // Get stats
        $stats = $leadModel->getStatsByTenant($tenantId);
        $agents = $userModel->getAgentsByTenant($tenantId);
        $subscription = $subscriptionModel->getActiveSubscription($tenantId);
        $usage = $usageModel->getCurrentUsage($tenantId);

        // Recent leads
        $recentLeads = $leadModel->getLeadsByTenant($tenantId, [], 10, 0);

        // Leads by source
        $leadsBySource = $leadModel->getLeadsBySource($tenantId);

        // Leads by city
        $leadsByCity = $leadModel->getLeadsByCity($tenantId);

        // Agent leaderboard
        $agentStats = [];
        foreach ($agents as $agent) {
            $agentWithMetrics = $userModel->getAgentWithMetrics($agent['id']);
            if ($agentWithMetrics) {
                $agentStats[] = $agentWithMetrics;
            }
        }

        // Sort by conversion rate
        usort($agentStats, function($a, $b) {
            return $b['conversion_rate'] - $a['conversion_rate'];
        });

        $data = [
            'stats' => $stats,
            'agents' => $agents,
            'subscription' => $subscription,
            'usage' => $usage,
            'recent_leads' => $recentLeads,
            'leads_by_source' => $leadsBySource,
            'leads_by_city' => $leadsByCity,
            'agent_leaderboard' => array_slice($agentStats, 0, 10)
        ];

        $this->view('dashboard/tenant_admin', $data);
    }

    /**
     * Agent dashboard
     */
    private function agentDashboard() {
        $agentId = $this->getCurrentUserId();
        $tenantId = $this->getCurrentTenantId();

        $leadModel = $this->model('Lead');
        $userModel = $this->model('User');

        // Get agent with metrics
        $agentData = $userModel->getAgentWithMetrics($agentId);

        // Get agent's leads
        $myLeads = $leadModel->getLeadsByAgent($agentId, [], 20, 0);

        // Get today's leads
        $todayLeads = $leadModel->getLeadsByAgent($agentId, [], 100, 0);
        $todayCount = 0;
        $today = date('Y-m-d');
        foreach ($todayLeads as $lead) {
            if (strpos($lead['assigned_at'], $today) === 0) {
                $todayCount++;
            }
        }

        // Status breakdown
        $statusCounts = [
            'new' => 0,
            'assigned' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'closed_won' => 0,
            'closed_lost' => 0
        ];

        foreach ($myLeads as $lead) {
            if (isset($statusCounts[$lead['status']])) {
                $statusCounts[$lead['status']]++;
            }
        }

        $data = [
            'agent' => $agentData,
            'my_leads' => $myLeads,
            'today_count' => $todayCount,
            'status_counts' => $statusCounts
        ];

        $this->view('dashboard/agent', $data);
    }
}
