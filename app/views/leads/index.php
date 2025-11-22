<?php
// FILE: /app/views/leads/index.php
$pageTitle = 'Leads';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Leads</h1>
    <a href="/leads/create" class="btn btn-primary">Add New Lead</a>
</div>

<!-- Filters -->
<div class="filters">
    <form method="GET" action="/leads" class="filter-form">
        <input type="text" name="search" placeholder="Search by name, phone, email..."
               value="<?php echo View::escape($_GET['search'] ?? ''); ?>">

        <select name="status">
            <option value="">All Statuses</option>
            <option value="new" <?php echo (isset($filters['status']) && $filters['status'] === 'new') ? 'selected' : ''; ?>>New</option>
            <option value="assigned" <?php echo (isset($filters['status']) && $filters['status'] === 'assigned') ? 'selected' : ''; ?>>Assigned</option>
            <option value="contacted" <?php echo (isset($filters['status']) && $filters['status'] === 'contacted') ? 'selected' : ''; ?>>Contacted</option>
            <option value="qualified" <?php echo (isset($filters['status']) && $filters['status'] === 'qualified') ? 'selected' : ''; ?>>Qualified</option>
            <option value="closed_won" <?php echo (isset($filters['status']) && $filters['status'] === 'closed_won') ? 'selected' : ''; ?>>Closed Won</option>
            <option value="closed_lost" <?php echo (isset($filters['status']) && $filters['status'] === 'closed_lost') ? 'selected' : ''; ?>>Closed Lost</option>
        </select>

        <?php if (in_array($_SESSION['user_role'], ['tenant_admin', 'platform_admin'])): ?>
        <select name="agent">
            <option value="">All Agents</option>
            <?php foreach ($agents as $agent): ?>
            <option value="<?php echo $agent['id']; ?>"
                    <?php echo (isset($filters['assigned_agent_id']) && $filters['assigned_agent_id'] == $agent['id']) ? 'selected' : ''; ?>>
                <?php echo View::escape($agent['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <button type="submit" class="btn">Filter</button>
        <a href="/leads" class="btn btn-secondary">Clear</a>
    </form>
</div>

<!-- Leads Table -->
<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Source</th>
                <th>Location</th>
                <th>Status</th>
                <th>Agent</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($leads)): ?>
            <tr>
                <td colspan="9" style="text-align: center;">No leads found</td>
            </tr>
            <?php else: ?>
            <?php foreach ($leads as $lead): ?>
            <tr>
                <td><a href="/leads/<?php echo $lead['id']; ?>"><?php echo View::escape($lead['name']); ?></a></td>
                <td><?php echo View::escape($lead['phone']); ?></td>
                <td><?php echo View::escape($lead['email']); ?></td>
                <td><?php echo View::escape($lead['source']); ?></td>
                <td><?php echo View::escape($lead['preferred_city']); ?></td>
                <td><span class="badge badge-<?php echo $lead['status']; ?>"><?php echo View::escape($lead['status']); ?></span></td>
                <td><?php echo View::escape($lead['agent_name'] ?? 'Unassigned'); ?></td>
                <td><?php echo View::formatDate($lead['created_at'], 'M d, Y'); ?></td>
                <td>
                    <a href="/leads/<?php echo $lead['id']; ?>" class="btn btn-sm">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($current_page > 1): ?>
    <a href="?page=<?php echo $current_page - 1; ?>&<?php echo http_build_query($filters); ?>" class="btn">Previous</a>
    <?php endif; ?>

    <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

    <?php if ($current_page < $total_pages): ?>
    <a href="?page=<?php echo $current_page + 1; ?>&<?php echo http_build_query($filters); ?>" class="btn">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
