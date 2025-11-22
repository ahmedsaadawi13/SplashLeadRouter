<?php
// FILE: /app/views/dashboard/agent.php
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard">
    <h1>Welcome, <?php echo View::escape($agent['name']); ?></h1>

    <!-- Agent Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Leads</h3>
            <p class="stat-number"><?php echo $agent['total_leads']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Today's Leads</h3>
            <p class="stat-number"><?php echo $today_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Leads</h3>
            <p class="stat-number"><?php echo $agent['active_leads_count']; ?> / <?php echo $agent['max_active_leads']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Conversion Rate</h3>
            <p class="stat-number"><?php echo number_format($agent['conversion_rate'], 1); ?>%</p>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="status-breakdown">
        <h2>My Leads by Status</h2>
        <div class="status-grid">
            <?php foreach ($status_counts as $status => $count): ?>
            <div class="status-item">
                <span class="badge badge-<?php echo $status; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                <span class="count"><?php echo $count; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- My Leads -->
    <div class="my-leads">
        <h2>My Assigned Leads</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($my_leads as $lead): ?>
                <tr>
                    <td><a href="/leads/<?php echo $lead['id']; ?>"><?php echo View::escape($lead['name']); ?></a></td>
                    <td><?php echo View::escape($lead['phone']); ?></td>
                    <td><?php echo View::escape($lead['preferred_city'] . ', ' . $lead['preferred_area']); ?></td>
                    <td><span class="badge badge-<?php echo $lead['status']; ?>"><?php echo View::escape($lead['status']); ?></span></td>
                    <td><?php echo View::formatDate($lead['assigned_at'], 'M d'); ?></td>
                    <td><a href="/leads/<?php echo $lead['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
