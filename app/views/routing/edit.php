<?php
// FILE: /app/views/routing/edit.php
$pageTitle = 'Edit Routing Rule';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Edit Routing Rule</h1>
    <a href="/routing-rules/<?php echo $rule['id']; ?>" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/routing-rules/<?php echo $rule['id']; ?>/update" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Rule Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape($rule['name']); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="priority">Priority</label>
                <input type="number" id="priority" name="priority" value="<?php echo $rule['priority']; ?>" min="1">
                <small>Lower number = higher priority</small>
            </div>

            <div class="form-group">
                <label for="assignment_strategy">Assignment Strategy</label>
                <select id="assignment_strategy" name="assignment_strategy">
                    <option value="round_robin" <?php echo $rule['assignment_strategy'] === 'round_robin' ? 'selected' : ''; ?>>Round Robin</option>
                    <option value="least_active" <?php echo $rule['assignment_strategy'] === 'least_active' ? 'selected' : ''; ?>>Least Active</option>
                    <option value="priority_based" <?php echo $rule['assignment_strategy'] === 'priority_based' ? 'selected' : ''; ?>>Priority Based</option>
                    <option value="weighted" <?php echo $rule['assignment_strategy'] === 'weighted' ? 'selected' : ''; ?>>Weighted</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?php echo $rule['is_active'] ? 'checked' : ''; ?>>
                Active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Rule</button>
            <a href="/routing-rules/<?php echo $rule['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
