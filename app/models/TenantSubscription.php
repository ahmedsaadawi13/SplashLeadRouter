<?php
// FILE: /app/models/TenantSubscription.php

require_once __DIR__ . '/../core/Model.php';

/**
 * TenantSubscription Model
 * Manages tenant subscriptions
 */
class TenantSubscription extends Model {
    protected $table = 'tenant_subscriptions';

    /**
     * Get active subscription for tenant
     * @param int $tenantId
     * @return array|null
     */
    public function getActiveSubscription($tenantId) {
        $sql = "SELECT ts.*, sp.name as plan_name, sp.max_agents, sp.max_leads_per_month,
                sp.max_zones, sp.max_routing_rules
                FROM {$this->table} ts
                JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE ts.tenant_id = ? AND ts.status IN ('active', 'trialing')
                ORDER BY ts.created_at DESC
                LIMIT 1";

        $stmt = $this->query($sql, [$tenantId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if tenant has exceeded limits
     * @param int $tenantId
     * @param string $limitType
     * @return bool
     */
    public function hasExceededLimit($tenantId, $limitType) {
        $subscription = $this->getActiveSubscription($tenantId);
        if (!$subscription) {
            return true;
        }

        require_once __DIR__ . '/UsageTracking.php';
        $usageModel = new UsageTracking();
        $usage = $usageModel->getCurrentUsage($tenantId);

        switch ($limitType) {
            case 'agents':
                return $usage['total_agents'] >= $subscription['max_agents'];
            case 'leads':
                return $usage['total_leads'] >= $subscription['max_leads_per_month'];
            case 'zones':
                return $usage['total_zones'] >= $subscription['max_zones'];
            case 'routing_rules':
                return $usage['total_routing_rules'] >= $subscription['max_routing_rules'];
            default:
                return false;
        }
    }

    /**
     * Update subscription status
     * @param int $subscriptionId
     * @param string $status
     * @return bool
     */
    public function updateStatus($subscriptionId, $status) {
        return $this->update($subscriptionId, ['status' => $status]);
    }
}
