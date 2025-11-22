<?php
// FILE: /app/models/Zone.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Zone Model
 * Manages geographical zones/territories
 */
class Zone extends Model {
    protected $table = 'zones';

    /**
     * Get zones by tenant
     * @param int $tenantId
     * @param bool $activeOnly
     * @return array
     */
    public function getZonesByTenant($tenantId, $activeOnly = true) {
        $conditions = ['tenant_id' => $tenantId];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions);
    }

    /**
     * Get zone with assigned agents
     * @param int $zoneId
     * @return array|null
     */
    public function getZoneWithAgents($zoneId) {
        $zone = $this->findById($zoneId);
        if (!$zone) {
            return null;
        }

        // Get assigned agents
        $sql = "SELECT u.id, u.name, u.email, za.priority, za.weight, za.is_primary
                FROM zone_agents za
                JOIN users u ON za.agent_id = u.id
                WHERE za.zone_id = ?
                ORDER BY za.priority ASC, za.is_primary DESC";

        $stmt = $this->query($sql, [$zoneId]);
        $zone['agents'] = $stmt->fetchAll();

        return $zone;
    }

    /**
     * Find zone by location
     * @param int $tenantId
     * @param string $city
     * @param string $area
     * @return array|null
     */
    public function findByLocation($tenantId, $city, $area = null) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ? AND is_active = 1 AND city = ?";

        $params = [$tenantId, $city];

        if ($area) {
            $sql .= " AND (subarea = ? OR region = ?)";
            $params[] = $area;
            $params[] = $area;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Assign agent to zone
     * @param int $zoneId
     * @param int $agentId
     * @param int $priority
     * @param int $weight
     * @param bool $isPrimary
     * @return bool
     */
    public function assignAgent($zoneId, $agentId, $priority = 1, $weight = 1, $isPrimary = false) {
        $sql = "INSERT INTO zone_agents (zone_id, agent_id, priority, weight, is_primary)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE priority = ?, weight = ?, is_primary = ?";

        $params = [$zoneId, $agentId, $priority, $weight, $isPrimary ? 1 : 0, $priority, $weight, $isPrimary ? 1 : 0];

        $this->query($sql, $params);
        return true;
    }

    /**
     * Remove agent from zone
     * @param int $zoneId
     * @param int $agentId
     * @return bool
     */
    public function removeAgent($zoneId, $agentId) {
        $sql = "DELETE FROM zone_agents WHERE zone_id = ? AND agent_id = ?";
        $this->query($sql, [$zoneId, $agentId]);
        return true;
    }

    /**
     * Get agents for zone
     * @param int $zoneId
     * @return array
     */
    public function getAgents($zoneId) {
        $sql = "SELECT u.*, za.priority, za.weight, za.is_primary
                FROM zone_agents za
                JOIN users u ON za.agent_id = u.id
                WHERE za.zone_id = ? AND u.is_active = 1
                ORDER BY za.priority ASC, za.is_primary DESC";

        $stmt = $this->query($sql, [$zoneId]);
        return $stmt->fetchAll();
    }
}
