<?php
// FILE: /app/views/leads/show.php
$pageTitle = 'Lead Details';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Lead: <?php echo View::escape($lead['name']); ?></h1>
    <div>
        <a href="/leads/<?php echo $lead['id']; ?>/edit" class="btn">Edit</a>
        <a href="/leads" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="lead-details">
    <div class="details-grid">
        <div class="detail-section">
            <h2>Contact Information</h2>
            <dl>
                <dt>Name:</dt>
                <dd><?php echo View::escape($lead['name']); ?></dd>

                <dt>Phone:</dt>
                <dd><?php echo View::escape($lead['phone']); ?></dd>

                <dt>Email:</dt>
                <dd><?php echo View::escape($lead['email'] ?? 'N/A'); ?></dd>

                <dt>Source:</dt>
                <dd><?php echo View::escape($lead['source']); ?></dd>

                <dt>Campaign:</dt>
                <dd><?php echo View::escape($lead['campaign'] ?? 'N/A'); ?></dd>
            </dl>
        </div>

        <div class="detail-section">
            <h2>Property Details</h2>
            <dl>
                <dt>Property Type:</dt>
                <dd><?php echo View::escape($lead['property_type'] ?? 'N/A'); ?></dd>

                <dt>Budget Range:</dt>
                <dd><?php echo $lead['budget_min'] ? View::formatCurrency($lead['budget_min']) : 'N/A'; ?> - <?php echo $lead['budget_max'] ? View::formatCurrency($lead['budget_max']) : 'N/A'; ?></dd>

                <dt>Preferred City:</dt>
                <dd><?php echo View::escape($lead['preferred_city'] ?? 'N/A'); ?></dd>

                <dt>Preferred Area:</dt>
                <dd><?php echo View::escape($lead['preferred_area'] ?? 'N/A'); ?></dd>
            </dl>
        </div>

        <div class="detail-section">
            <h2>Assignment & Status</h2>
            <dl>
                <dt>Current Status:</dt>
                <dd><span class="badge badge-<?php echo $lead['status']; ?>"><?php echo View::escape($lead['status']); ?></span></dd>

                <dt>Assigned To:</dt>
                <dd><?php echo View::escape($lead['agent_name'] ?? 'Unassigned'); ?></dd>

                <dt>Assigned At:</dt>
                <dd><?php echo $lead['assigned_at'] ? View::formatDate($lead['assigned_at']) : 'N/A'; ?></dd>

                <dt>Created:</dt>
                <dd><?php echo View::formatDate($lead['created_at']); ?></dd>

                <dt>Last Updated:</dt>
                <dd><?php echo View::formatDate($lead['updated_at']); ?></dd>
            </dl>
        </div>
    </div>

    <!-- Update Status -->
    <div class="status-update">
        <h2>Update Status</h2>
        <form method="POST" action="/leads/<?php echo $lead['id']; ?>/status" class="inline-form">
            <?php echo View::csrfField(); ?>
            <select name="status">
                <option value="new" <?php echo $lead['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="assigned" <?php echo $lead['status'] === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                <option value="contacted" <?php echo $lead['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                <option value="qualified" <?php echo $lead['status'] === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                <option value="unqualified" <?php echo $lead['status'] === 'unqualified' ? 'selected' : ''; ?>>Unqualified</option>
                <option value="closed_won" <?php echo $lead['status'] === 'closed_won' ? 'selected' : ''; ?>>Closed Won</option>
                <option value="closed_lost" <?php echo $lead['status'] === 'closed_lost' ? 'selected' : ''; ?>>Closed Lost</option>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>

    <!-- Notes -->
    <div class="notes-section">
        <h2>Notes</h2>

        <form method="POST" action="/leads/<?php echo $lead['id']; ?>/notes" class="note-form">
            <?php echo View::csrfField(); ?>
            <textarea name="note" rows="3" placeholder="Add a note..." required></textarea>
            <button type="submit" class="btn btn-primary">Add Note</button>
        </form>

        <div class="notes-list">
            <?php if (empty($notes)): ?>
            <p>No notes yet.</p>
            <?php else: ?>
            <?php foreach ($notes as $note): ?>
            <div class="note-item">
                <div class="note-header">
                    <strong><?php echo View::escape($note['user_name']); ?></strong>
                    <span><?php echo View::formatDate($note['created_at']); ?></span>
                </div>
                <div class="note-body">
                    <?php echo nl2br(View::escape($note['note'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Routing Logs -->
    <?php if (!empty($logs)): ?>
    <div class="routing-logs">
        <h2>Routing History</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Rule</th>
                    <th>Zone</th>
                    <th>Reason</th>
                    <th>Strategy</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo View::escape($log['agent_name'] ?? 'N/A'); ?></td>
                    <td><?php echo View::escape($log['rule_name'] ?? 'N/A'); ?></td>
                    <td><?php echo View::escape($log['zone_name'] ?? 'N/A'); ?></td>
                    <td><?php echo View::escape($log['reason']); ?></td>
                    <td><?php echo View::escape($log['strategy_used']); ?></td>
                    <td><?php echo View::formatDate($log['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
