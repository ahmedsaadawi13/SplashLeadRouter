<?php
// FILE: /app/models/Payment.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Payment Model
 * Manages payment transactions
 */
class Payment extends Model {
    protected $table = 'payments';

    /**
     * Get payments by tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPaymentsByTenant($tenantId, $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = ?
                ORDER BY p.payment_date DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get payments by invoice
     * @param int $invoiceId
     * @return array
     */
    public function getPaymentsByInvoice($invoiceId) {
        return $this->findAll(['invoice_id' => $invoiceId]);
    }

    /**
     * Process payment (simulated)
     * @param array $data
     * @return int
     */
    public function processPayment($data) {
        // In real implementation, this would integrate with payment gateway
        $data['status'] = 'success';
        $data['transaction_id'] = 'txn_' . uniqid();
        $data['payment_date'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }
}
