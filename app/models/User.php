<?php
// FILE: /app/models/User.php

require_once __DIR__ . '/../core/Model.php';

/**
 * User Model
 * Handles user authentication and management
 */
class User extends Model {
    protected $table = 'users';

    /**
     * Find user by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        return $this->findOne(['email' => $email]);
    }

    /**
     * Create user with hashed password
     * @param array $data
     * @return int
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    /**
     * Verify user credentials
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function verifyCredentials($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Get agents by tenant
     * @param int $tenantId
     * @param bool $activeOnly
     * @return array
     */
    public function getAgentsByTenant($tenantId, $activeOnly = true) {
        $conditions = [
            'tenant_id' => $tenantId,
            'role' => 'agent'
        ];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions);
    }

    /**
     * Get agent with performance metrics
     * @param int $agentId
     * @return array|null
     */
    public function getAgentWithMetrics($agentId) {
        $sql = "SELECT u.*,
                COUNT(DISTINCT l.id) as total_leads,
                COUNT(DISTINCT CASE WHEN l.status = 'closed_won' THEN l.id END) as closed_won_count,
                COUNT(DISTINCT CASE WHEN l.status IN ('new', 'assigned', 'contacted', 'qualified') THEN l.id END) as active_leads_count,
                ROUND(COUNT(DISTINCT CASE WHEN l.status = 'closed_won' THEN l.id END) * 100.0 / NULLIF(COUNT(DISTINCT l.id), 0), 2) as conversion_rate
                FROM {$this->table} u
                LEFT JOIN leads l ON u.id = l.assigned_agent_id
                WHERE u.id = ?
                GROUP BY u.id";

        $stmt = $this->query($sql, [$agentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get agents capacity status
     * @param int $tenantId
     * @return array
     */
    public function getAgentsCapacity($tenantId) {
        $sql = "SELECT u.id, u.name, u.email, u.max_active_leads,
                COUNT(DISTINCT CASE WHEN l.status IN ('new', 'assigned', 'contacted', 'qualified') THEN l.id END) as current_active_leads,
                (u.max_active_leads - COUNT(DISTINCT CASE WHEN l.status IN ('new', 'assigned', 'contacted', 'qualified') THEN l.id END)) as available_capacity
                FROM {$this->table} u
                LEFT JOIN leads l ON u.id = l.assigned_agent_id
                WHERE u.tenant_id = ? AND u.role = 'agent' AND u.is_active = 1
                GROUP BY u.id
                ORDER BY available_capacity DESC";

        $stmt = $this->query($sql, [$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if agent has capacity
     * @param int $agentId
     * @return bool
     */
    public function hasCapacity($agentId) {
        $agent = $this->findById($agentId);
        if (!$agent) {
            return false;
        }

        $sql = "SELECT COUNT(*) as active_count
                FROM leads
                WHERE assigned_agent_id = ? AND status IN ('new', 'assigned', 'contacted', 'qualified')";

        $stmt = $this->query($sql, [$agentId]);
        $result = $stmt->fetch();

        return $result['active_count'] < $agent['max_active_leads'];
    }

    /**
     * Update user password
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }
}
