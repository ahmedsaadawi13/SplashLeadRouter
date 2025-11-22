<?php
// FILE: /app/models/LeadNote.php

require_once __DIR__ . '/../core/Model.php';

/**
 * LeadNote Model
 * Manages notes on leads
 */
class LeadNote extends Model {
    protected $table = 'lead_notes';

    /**
     * Get notes by lead
     * @param int $leadId
     * @return array
     */
    public function getNotesByLead($leadId) {
        $sql = "SELECT ln.*, u.name as user_name
                FROM {$this->table} ln
                JOIN users u ON ln.user_id = u.id
                WHERE ln.lead_id = ?
                ORDER BY ln.created_at DESC";

        $stmt = $this->query($sql, [$leadId]);
        return $stmt->fetchAll();
    }

    /**
     * Add note to lead
     * @param int $leadId
     * @param int $userId
     * @param string $note
     * @return int
     */
    public function addNote($leadId, $userId, $note) {
        return $this->create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'note' => $note
        ]);
    }
}
