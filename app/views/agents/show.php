<?php
// FILE: /app/views/agents/show.php
$pageTitle = 'Agent Details';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Agent: <?php echo View::escape($agent['name']); ?></h1>
    <div>
        <a href="/agents/<?php echo $agent['id']; ?>/edit" class="btn">Edit</a>
        <a href="/agents" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Leads</h3>
        <p class="stat-number"><?php echo $agent['total_leads']; ?></p>
    </div>
    <div class="stat-card">
        <h3>Active Leads</h3>
        <p class="stat-number"><?php echo $agent['active_leads_count']; ?> / <?php echo $agent['max_active_leads']; ?></p>
    </div>
    <div class="stat-card">
        <h3>Closed Won</h3>
        <p class="stat-number"><?php echo $agent['closed_won_count']; ?></p>
    </div>
    <div class="stat-card">
        <h3>Conversion Rate</h3>
        <p class="stat-number"><?php echo number_format($agent['conversion_rate'], 1); ?>%</p>
    </div>
</div>

<div class="agent-info">
    <h2>Information</h2>
    <dl>
        <dt>Email:</dt>
        <dd><?php echo View::escape($agent['email']); ?></dd>
        <dt>Phone:</dt>
        <dd><?php echo View::escape($agent['phone']); ?></dd>
        <dt>Level:</dt>
        <dd><?php echo View::escape($agent['agent_level']); ?></dd>
        <dt>Status:</dt>
        <dd><?php echo $agent['is_active'] ? 'Active' : 'Inactive'; ?></dd>
    </dl>
</div>

<div class="recent-leads">
    <h2>Recent Leads</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Assigned</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_leads as $lead): ?>
            <tr>
                <td><a href="/leads/<?php echo $lead['id']; ?>"><?php echo View::escape($lead['name']); ?></a></td>
                <td><?php echo View::escape($lead['phone']); ?></td>
                <td><span class="badge badge-<?php echo $lead['status']; ?>"><?php echo $lead['status']; ?></span></td>
                <td><?php echo View::formatDate($lead['assigned_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
