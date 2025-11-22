<?php
// FILE: /app/models/LeadSource.php

require_once __DIR__ . '/../core/Model.php';

/**
 * LeadSource Model
 * Manages lead sources
 */
class LeadSource extends Model {
    protected $table = 'lead_sources';

    /**
     * Get sources by tenant
     * @param int $tenantId
     * @param bool $activeOnly
     * @return array
     */
    public function getSourcesByTenant($tenantId, $activeOnly = true) {
        $conditions = ['tenant_id' => $tenantId];

        if ($activeOnly) {
            $conditions['is_active'] = 1;
        }

        return $this->findAll($conditions);
    }

    /**
     * Toggle source status
     * @param int $sourceId
     * @return bool
     */
    public function toggleStatus($sourceId) {
        $source = $this->findById($sourceId);
        if (!$source) {
            return false;
        }

        $newStatus = $source['is_active'] ? 0 : 1;
        return $this->update($sourceId, ['is_active' => $newStatus]);
    }
}
