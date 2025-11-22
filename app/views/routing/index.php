<?php
// FILE: /app/views/routing/index.php
$pageTitle = 'Routing Rules';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Routing Rules</h1>
    <a href="/routing-rules/create" class="btn btn-primary">Create New Rule</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Priority</th>
            <th>Name</th>
            <th>Type</th>
            <th>Strategy</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rules)): ?>
        <tr>
            <td colspan="6" style="text-align: center;">No routing rules found</td>
        </tr>
        <?php else: ?>
        <?php foreach ($rules as $rule): ?>
        <tr>
            <td><?php echo $rule['priority']; ?></td>
            <td><a href="/routing-rules/<?php echo $rule['id']; ?>"><?php echo View::escape($rule['name']); ?></a></td>
            <td><?php echo View::escape($rule['rule_type']); ?></td>
            <td><?php echo View::escape($rule['assignment_strategy']); ?></td>
            <td><span class="badge badge-<?php echo $rule['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $rule['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
            <td>
                <a href="/routing-rules/<?php echo $rule['id']; ?>" class="btn btn-sm">View</a>
                <a href="/routing-rules/<?php echo $rule['id']; ?>/edit" class="btn btn-sm">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($current_page > 1): ?>
    <a href="?page=<?php echo $current_page - 1; ?>" class="btn">Previous</a>
    <?php endif; ?>

    <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>

    <?php if ($current_page < $total_pages): ?>
    <a href="?page=<?php echo $current_page + 1; ?>" class="btn">Next</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
