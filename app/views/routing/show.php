<?php
// FILE: /app/views/routing/show.php
$pageTitle = 'Routing Rule Details';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Routing Rule: <?php echo View::escape($rule['name']); ?></h1>
    <div>
        <a href="/routing-rules/<?php echo $rule['id']; ?>/edit" class="btn">Edit</a>
        <a href="/routing-rules" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="rule-details">
    <h2>Rule Information</h2>
    <dl>
        <dt>Name:</dt>
        <dd><?php echo View::escape($rule['name']); ?></dd>

        <dt>Type:</dt>
        <dd><?php echo View::escape($rule['rule_type']); ?></dd>

        <dt>Priority:</dt>
        <dd><?php echo $rule['priority']; ?></dd>

        <dt>Assignment Strategy:</dt>
        <dd><?php echo View::escape($rule['assignment_strategy']); ?></dd>

        <dt>Conditions:</dt>
        <dd><pre><?php echo View::escape($rule['conditions']); ?></pre></dd>

        <dt>Status:</dt>
        <dd><span class="badge badge-<?php echo $rule['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $rule['is_active'] ? 'Active' : 'Inactive'; ?></span></dd>

        <dt>Created:</dt>
        <dd><?php echo View::formatDate($rule['created_at']); ?></dd>

        <dt>Last Updated:</dt>
        <dd><?php echo View::formatDate($rule['updated_at']); ?></dd>
    </dl>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
