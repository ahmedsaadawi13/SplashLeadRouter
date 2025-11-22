<?php
// FILE: /app/models/UsageTracking.php

require_once __DIR__ . '/../core/Model.php';

/**
 * UsageTracking Model
 * Tracks resource usage per tenant
 */
class UsageTracking extends Model {
    protected $table = 'usage_tracking';

    /**
     * Get current month usage
     * @param int $tenantId
     * @return array
     */
    public function getCurrentUsage($tenantId) {
        $currentMonth = date('Y-m');

        $usage = $this->findOne([
            'tenant_id' => $tenantId,
            'period_month' => $currentMonth
        ]);

        if (!$usage) {
            // Create initial usage record
            $this->create([
                'tenant_id' => $tenantId,
                'period_month' => $currentMonth,
                'total_leads' => 0,
                'total_agents' => 0,
                'total_zones' => 0,
                'total_routing_rules' => 0
            ]);

            $usage = $this->findOne([
                'tenant_id' => $tenantId,
                'period_month' => $currentMonth
            ]);
        }

        return $usage;
    }

    /**
     * Update usage counts
     * @param int $tenantId
     * @return bool
     */
    public function updateUsage($tenantId) {
        $currentMonth = date('Y-m');

        // Count actual usage
        $sql = "SELECT
                (SELECT COUNT(*) FROM leads WHERE tenant_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?) as total_leads,
                (SELECT COUNT(*) FROM users WHERE tenant_id = ? AND role = 'agent') as total_agents,
                (SELECT COUNT(*) FROM zones WHERE tenant_id = ?) as total_zones,
                (SELECT COUNT(*) FROM routing_rules WHERE tenant_id = ?) as total_routing_rules";

        $stmt = $this->query($sql, [$tenantId, $currentMonth, $tenantId, $tenantId, $tenantId]);
        $counts = $stmt->fetch();

        // Update or insert
        $existing = $this->findOne([
            'tenant_id' => $tenantId,
            'period_month' => $currentMonth
        ]);

        if ($existing) {
            return $this->update($existing['id'], $counts);
        } else {
            $counts['tenant_id'] = $tenantId;
            $counts['period_month'] = $currentMonth;
            $this->create($counts);
            return true;
        }
    }

    /**
     * Get usage history
     * @param int $tenantId
     * @param int $months
     * @return array
     */
    public function getUsageHistory($tenantId, $months = 12) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY period_month DESC
                LIMIT ?";

        $stmt = $this->query($sql, [$tenantId, $months]);
        return $stmt->fetchAll();
    }
}
