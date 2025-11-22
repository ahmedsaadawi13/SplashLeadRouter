<?php
// FILE: /app/views/dashboard/platform_admin.php
$pageTitle = 'Platform Admin Dashboard';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard">
    <h1>Platform Administration</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Active Tenants</h3>
            <p class="stat-number"><?php echo $active_tenants; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Leads</h3>
            <p class="stat-number"><?php echo number_format($total_leads); ?></p>
        </div>
    </div>

    <div class="tenants-list">
        <h2>All Tenants</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agency Name</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                <tr>
                    <td><?php echo View::escape($tenant['name']); ?></td>
                    <td><?php echo View::escape($tenant['email']); ?></td>
                    <td><?php echo View::escape($tenant['city']); ?></td>
                    <td><span class="badge badge-<?php echo $tenant['status']; ?>"><?php echo View::escape($tenant['status']); ?></span></td>
                    <td><?php echo View::formatDate($tenant['created_at'], 'M d, Y'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
