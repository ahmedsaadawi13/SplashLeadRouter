<?php
// FILE: /app/models/RoutingRule.php

require_once __DIR__ . '/../core/Model.php';

/**
 * RoutingRule Model
 * Manages lead routing rules
 */
class RoutingRule extends Model {
    protected $table = 'routing_rules';

    /**
     * Get active rules by tenant
     * @param int $tenantId
     * @return array
     */
    public function getActiveRules($tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND is_active = 1
                ORDER BY priority ASC";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get rules by tenant with pagination
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRulesByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY priority ASC, created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Check if rule matches lead
     * @param array $rule
     * @param array $lead
     * @return bool
     */
    public function matchesLead($rule, $lead) {
        if (empty($rule['conditions'])) {
            return false;
        }

        $conditions = json_decode($rule['conditions'], true);
        if (!$conditions) {
            return false;
        }

        // Budget-based matching
        if ($rule['rule_type'] === 'budget_based') {
            if (isset($conditions['budget_min']) && $lead['budget_max']) {
                if ($lead['budget_max'] < $conditions['budget_min']) {
                    return false;
                }
            }
            if (isset($conditions['budget_max']) && $lead['budget_min']) {
                if ($lead['budget_min'] > $conditions['budget_max']) {
                    return false;
                }
            }
        }

        // Property type matching
        if ($rule['rule_type'] === 'property_type_based') {
            if (isset($conditions['property_type']) && $lead['property_type']) {
                if (strtolower($conditions['property_type']) !== strtolower($lead['property_type'])) {
                    return false;
                }
            }
        }

        // Source matching
        if ($rule['rule_type'] === 'source_based') {
            if (isset($conditions['source']) && $lead['source']) {
                if (strtolower($conditions['source']) !== strtolower($lead['source'])) {
                    return false;
                }
            }
        }

        // Zone matching (requires additional zone lookup)
        if ($rule['rule_type'] === 'zone_based') {
            // This would need zone lookup based on lead location
            // Simplified check here
            if (isset($conditions['zones']) && is_array($conditions['zones'])) {
                // Would check if lead's city/area matches any zone
                return true;
            }
        }

        return true;
    }

    /**
     * Get target agents from rule
     * @param array $rule
     * @return array
     */
    public function getTargetAgents($rule) {
        if (empty($rule['target_agent_ids'])) {
            return [];
        }

        $agentIds = json_decode($rule['target_agent_ids'], true);
        if (!$agentIds || !is_array($agentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($agentIds), '?'));

        $sql = "SELECT * FROM users
                WHERE id IN ({$placeholders}) AND is_active = 1";

        $stmt = $this->query($sql, $agentIds);
        return $stmt->fetchAll();
    }

    /**
     * Update rule priority
     * @param int $ruleId
     * @param int $priority
     * @return bool
     */
    public function updatePriority($ruleId, $priority) {
        return $this->update($ruleId, ['priority' => $priority]);
    }

    /**
     * Toggle rule status
     * @param int $ruleId
     * @return bool
     */
    public function toggleStatus($ruleId) {
        $rule = $this->findById($ruleId);
        if (!$rule) {
            return false;
        }

        $newStatus = $rule['is_active'] ? 0 : 1;
        return $this->update($ruleId, ['is_active' => $newStatus]);
    }
}
