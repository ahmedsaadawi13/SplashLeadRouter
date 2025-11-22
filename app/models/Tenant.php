<?php
// FILE: /app/models/Tenant.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Tenant Model
 * Represents real estate agencies using the platform
 */
class Tenant extends Model {
    protected $table = 'tenants';

    /**
     * Find tenant by slug
     * @param string $slug
     * @return array|null
     */
    public function findBySlug($slug) {
        return $this->findOne(['slug' => $slug]);
    }

    /**
     * Get active tenants
     * @return array
     */
    public function getActiveTenants() {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Get tenant with subscription
     * @param int $id
     * @return array|null
     */
    public function getTenantWithSubscription($id) {
        $sql = "SELECT t.*, ts.status as subscription_status, sp.name as plan_name
                FROM {$this->table} t
                LEFT JOIN tenant_subscriptions ts ON t.id = ts.tenant_id
                LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
                WHERE t.id = ?
                LIMIT 1";

        $stmt = $this->query($sql, [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Update tenant status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
}
