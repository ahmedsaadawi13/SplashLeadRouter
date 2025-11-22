<?php
// FILE: /app/views/agents/create.php
$pageTitle = 'Create Agent';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Create New Agent</h1>
    <a href="/agents" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/agents" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape(View::old('name')); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?php echo View::escape(View::old('email')); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 characters</small>
        </div>

        <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="tel" id="phone" name="phone" value="<?php echo View::escape(View::old('phone')); ?>" required>
        </div>

        <div class="form-group">
            <label for="agent_level">Level</label>
            <select id="agent_level" name="agent_level">
                <option value="junior">Junior</option>
                <option value="senior">Senior</option>
            </select>
        </div>

        <div class="form-group">
            <label for="max_active_leads">Max Active Leads</label>
            <input type="number" id="max_active_leads" name="max_active_leads" value="10" min="1">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Agent</button>
            <a href="/agents" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
