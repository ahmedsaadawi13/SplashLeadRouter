<?php
// FILE: /app/models/Lead.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Lead Model
 * Manages real estate leads
 */
class Lead extends Model {
    protected $table = 'leads';

    /**
     * Get leads by tenant with pagination
     * @param int $tenantId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLeadsByTenant($tenantId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT l.*, u.name as agent_name, ls.name as source_name
                FROM {$this->table} l
                LEFT JOIN users u ON l.assigned_agent_id = u.id
                LEFT JOIN lead_sources ls ON l.source_id = ls.id
                WHERE l.tenant_id = ?";

        $params = [$tenantId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['source'])) {
            $sql .= " AND l.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['assigned_agent_id'])) {
            $sql .= " AND l.assigned_agent_id = ?";
            $params[] = $filters['assigned_agent_id'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND l.preferred_city = ?";
            $params[] = $filters['city'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get leads by agent
     * @param int $agentId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLeadsByAgent($agentId, $filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT l.*, ls.name as source_name
                FROM {$this->table} l
                LEFT JOIN lead_sources ls ON l.source_id = ls.id
                WHERE l.assigned_agent_id = ?";

        $params = [$agentId];

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Count leads by tenant
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['source'])) {
            $sql .= " AND source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['assigned_agent_id'])) {
            $sql .= " AND assigned_agent_id = ?";
            $params[] = $filters['assigned_agent_id'];
        }

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Assign lead to agent
     * @param int $leadId
     * @param int $agentId
     * @param int $ruleId
     * @return bool
     */
    public function assignToAgent($leadId, $agentId, $ruleId = null) {
        $data = [
            'assigned_agent_id' => $agentId,
            'assigned_at' => date('Y-m-d H:i:s'),
            'status' => 'assigned'
        ];

        if ($ruleId) {
            $data['assignment_rule_id'] = $ruleId;
        }

        return $this->update($leadId, $data);
    }

    /**
     * Update lead status
     * @param int $leadId
     * @param string $status
     * @return bool
     */
    public function updateStatus($leadId, $status) {
        $data = ['status' => $status];

        // Set timestamp based on status
        if ($status === 'contacted' && !$this->findById($leadId)['first_contacted_at']) {
            $data['first_contacted_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'qualified') {
            $data['qualified_at'] = date('Y-m-d H:i:s');
        } elseif (in_array($status, ['closed_won', 'closed_lost'])) {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($leadId, $data);
    }

    /**
     * Get lead statistics by tenant
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getStatsByTenant($tenantId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT
                COUNT(*) as total_leads,
                COUNT(CASE WHEN status = 'new' THEN 1 END) as new_leads,
                COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned_leads,
                COUNT(CASE WHEN status = 'contacted' THEN 1 END) as contacted_leads,
                COUNT(CASE WHEN status = 'qualified' THEN 1 END) as qualified_leads,
                COUNT(CASE WHEN status = 'closed_won' THEN 1 END) as won_leads,
                COUNT(CASE WHEN status = 'closed_lost' THEN 1 END) as lost_leads,
                COUNT(CASE WHEN assigned_agent_id IS NULL THEN 1 END) as unassigned_leads
                FROM {$this->table}
                WHERE tenant_id = ?";

        $params = [$tenantId];

        if ($dateFrom) {
            $sql .= " AND created_at >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND created_at <= ?";
            $params[] = $dateTo;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get leads by source
     * @param int $tenantId
     * @return array
     */
    public function getLeadsBySource($tenantId) {
        $sql = "SELECT source, COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = ? AND source IS NOT NULL
                GROUP BY source
                ORDER BY count DESC";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get leads by city
     * @param int $tenantId
     * @return array
     */
    public function getLeadsByCity($tenantId) {
        $sql = "SELECT preferred_city, COUNT(*) as count
                FROM {$this->table}
                WHERE tenant_id = ? AND preferred_city IS NOT NULL
                GROUP BY preferred_city
                ORDER BY count DESC";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Bulk import leads
     * @param array $leads
     * @return int Number of imported leads
     */
    public function bulkImport($leads) {
        $imported = 0;

        foreach ($leads as $lead) {
            try {
                $this->create($lead);
                $imported++;
            } catch (Exception $e) {
                // Log error but continue importing
                continue;
            }
        }

        return $imported;
    }
}
