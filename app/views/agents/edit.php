<?php
// FILE: /app/views/agents/edit.php
$pageTitle = 'Edit Agent';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Edit Agent</h1>
    <a href="/agents/<?php echo $agent['id']; ?>" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/agents/<?php echo $agent['id']; ?>/update" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape($agent['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="tel" id="phone" name="phone" value="<?php echo View::escape($agent['phone']); ?>" required>
        </div>

        <div class="form-group">
            <label for="agent_level">Level</label>
            <select id="agent_level" name="agent_level">
                <option value="junior" <?php echo $agent['agent_level'] === 'junior' ? 'selected' : ''; ?>>Junior</option>
                <option value="senior" <?php echo $agent['agent_level'] === 'senior' ? 'selected' : ''; ?>>Senior</option>
            </select>
        </div>

        <div class="form-group">
            <label for="max_active_leads">Max Active Leads</label>
            <input type="number" id="max_active_leads" name="max_active_leads" value="<?php echo $agent['max_active_leads']; ?>" min="1">
        </div>

        <div class="form-group">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?php echo $agent['is_active'] ? 'checked' : ''; ?>>
                Active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Agent</button>
            <a href="/agents/<?php echo $agent['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
