<?php
// FILE: /app/models/Invoice.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Invoice Model
 * Manages billing invoices
 */
class Invoice extends Model {
    protected $table = 'invoices';

    /**
     * Get invoices by tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getInvoicesByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Generate invoice number
     * @return string
     */
    public function generateInvoiceNumber() {
        $year = date('Y');
        $month = date('m');

        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE invoice_number LIKE ?";

        $stmt = $this->query($sql, ["INV-{$year}-{$month}%"]);
        $result = $stmt->fetch();

        $sequence = str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
        return "INV-{$year}-{$month}-{$sequence}";
    }

    /**
     * Mark invoice as paid
     * @param int $invoiceId
     * @return bool
     */
    public function markAsPaid($invoiceId) {
        return $this->update($invoiceId, [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }
}
