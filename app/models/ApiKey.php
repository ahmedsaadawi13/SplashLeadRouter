<?php
// FILE: /app/models/ApiKey.php

require_once __DIR__ . '/../core/Model.php';

/**
 * ApiKey Model
 * Manages API keys for tenant authentication
 */
class ApiKey extends Model {
    protected $table = 'api_keys';

    /**
     * Verify API key and return tenant
     * @param string $apiKey
     * @return array|null Tenant data or null
     */
    public function verifyKey($apiKey) {
        $sql = "SELECT ak.*, t.id as tenant_id, t.name as tenant_name, t.status as tenant_status
                FROM {$this->table} ak
                JOIN tenants t ON ak.tenant_id = t.id
                WHERE ak.api_key = ? AND ak.is_active = 1 AND t.status = 'active'
                LIMIT 1";

        $stmt = $this->query($sql, [$apiKey]);
        $result = $stmt->fetch();

        if ($result) {
            // Update last used timestamp
            $this->update($result['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        return $result ?: null;
    }

    /**
     * Generate new API key
     * @param int $tenantId
     * @param string $name
     * @return string
     */
    public function generateKey($tenantId, $name = 'API Key') {
        $apiKey = 'slr_' . bin2hex(random_bytes(32));

        $this->create([
            'tenant_id' => $tenantId,
            'api_key' => $apiKey,
            'name' => $name,
            'is_active' => 1
        ]);

        return $apiKey;
    }

    /**
     * Get keys by tenant
     * @param int $tenantId
     * @return array
     */
    public function getKeysByTenant($tenantId) {
        return $this->findAll(['tenant_id' => $tenantId]);
    }

    /**
     * Revoke API key
     * @param int $keyId
     * @return bool
     */
    public function revokeKey($keyId) {
        return $this->update($keyId, ['is_active' => 0]);
    }
}
