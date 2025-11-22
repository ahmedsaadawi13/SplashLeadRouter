<?php
// FILE: /app/models/RoutingLog.php

require_once __DIR__ . '/../core/Model.php';

/**
 * RoutingLog Model
 * Logs routing decisions
 */
class RoutingLog extends Model {
    protected $table = 'routing_logs';

    /**
     * Log routing decision
     * @param array $data
     * @return int
     */
    public function logDecision($data) {
        return $this->create($data);
    }

    /**
     * Get logs by lead
     * @param int $leadId
     * @return array
     */
    public function getLogsByLead($leadId) {
        $sql = "SELECT rl.*, u.name as agent_name, rr.name as rule_name, z.name as zone_name
                FROM {$this->table} rl
                LEFT JOIN users u ON rl.selected_agent_id = u.id
                LEFT JOIN routing_rules rr ON rl.rule_id = rr.id
                LEFT JOIN zones z ON rl.zone_id = z.id
                WHERE rl.lead_id = ?
                ORDER BY rl.created_at DESC";

        $stmt = $this->query($sql, [$leadId]);
        return $stmt->fetchAll();
    }

    /**
     * Get logs by tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogsByTenant($tenantId, $limit = 50, $offset = 0) {
        $sql = "SELECT rl.*, u.name as agent_name, l.name as lead_name, rr.name as rule_name
                FROM {$this->table} rl
                LEFT JOIN users u ON rl.selected_agent_id = u.id
                LEFT JOIN leads l ON rl.lead_id = l.id
                LEFT JOIN routing_rules rr ON rl.rule_id = rr.id
                WHERE rl.tenant_id = ?
                ORDER BY rl.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }
}
