<?php
// FILE: /app/views/dashboard/tenant_admin.php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard">
    <h1>Dashboard</h1>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Leads</h3>
            <p class="stat-number"><?php echo number_format($stats['total_leads']); ?></p>
        </div>
        <div class="stat-card">
            <h3>New Leads</h3>
            <p class="stat-number"><?php echo number_format($stats['new_leads']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Contacted</h3>
            <p class="stat-number"><?php echo number_format($stats['contacted_leads']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Won</h3>
            <p class="stat-number"><?php echo number_format($stats['won_leads']); ?></p>
        </div>
    </div>

    <!-- Usage & Subscription -->
    <?php if ($subscription): ?>
    <div class="subscription-info">
        <h2>Subscription: <?php echo View::escape($subscription['plan_name']); ?></h2>
        <div class="usage-bars">
            <div class="usage-item">
                <label>Leads this month: <?php echo $usage['total_leads']; ?> / <?php echo $subscription['max_leads_per_month']; ?></label>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min(100, ($usage['total_leads'] / $subscription['max_leads_per_month']) * 100); ?>%"></div>
                </div>
            </div>
            <div class="usage-item">
                <label>Agents: <?php echo $usage['total_agents']; ?> / <?php echo $subscription['max_agents']; ?></label>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min(100, ($usage['total_agents'] / $subscription['max_agents']) * 100); ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Leads -->
    <div class="recent-leads">
        <h2>Recent Leads</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Agent</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_leads as $lead): ?>
                <tr>
                    <td><a href="/leads/<?php echo $lead['id']; ?>"><?php echo View::escape($lead['name']); ?></a></td>
                    <td><?php echo View::escape($lead['phone']); ?></td>
                    <td><?php echo View::escape($lead['source']); ?></td>
                    <td><span class="badge badge-<?php echo $lead['status']; ?>"><?php echo View::escape($lead['status']); ?></span></td>
                    <td><?php echo View::escape($lead['agent_name'] ?? 'Unassigned'); ?></td>
                    <td><?php echo View::formatDate($lead['created_at'], 'M d, Y'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Agent Leaderboard -->
    <div class="leaderboard">
        <h2>Top Performing Agents</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Total Leads</th>
                    <th>Closed Won</th>
                    <th>Active Leads</th>
                    <th>Conversion Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agent_leaderboard as $agent): ?>
                <tr>
                    <td><a href="/agents/<?php echo $agent['id']; ?>"><?php echo View::escape($agent['name']); ?></a></td>
                    <td><?php echo $agent['total_leads']; ?></td>
                    <td><?php echo $agent['closed_won_count']; ?></td>
                    <td><?php echo $agent['active_leads_count']; ?></td>
                    <td><?php echo number_format($agent['conversion_rate'], 1); ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
