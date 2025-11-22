<?php
// FILE: /app/views/routing/create.php
$pageTitle = 'Create Routing Rule';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Create New Routing Rule</h1>
    <a href="/routing-rules" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/routing-rules" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Rule Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape(View::old('name')); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="rule_type">Rule Type *</label>
                <select id="rule_type" name="rule_type" required>
                    <option value="">Select Type</option>
                    <option value="zone_based">Zone Based</option>
                    <option value="budget_based">Budget Based</option>
                    <option value="property_type_based">Property Type Based</option>
                    <option value="source_based">Source Based</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Priority</label>
                <input type="number" id="priority" name="priority" value="1" min="1">
                <small>Lower number = higher priority</small>
            </div>
        </div>

        <div class="form-group">
            <label for="assignment_strategy">Assignment Strategy</label>
            <select id="assignment_strategy" name="assignment_strategy">
                <option value="round_robin">Round Robin</option>
                <option value="least_active">Least Active</option>
                <option value="priority_based">Priority Based</option>
                <option value="weighted">Weighted</option>
            </select>
        </div>

        <div class="form-group">
            <label for="budget_min">Budget Min (for budget-based rules)</label>
            <input type="number" id="budget_min" name="budget_min">
        </div>

        <div class="form-group">
            <label for="budget_max">Budget Max (for budget-based rules)</label>
            <input type="number" id="budget_max" name="budget_max">
        </div>

        <div class="form-group">
            <label for="property_type">Property Type (for property-type rules)</label>
            <input type="text" id="property_type" name="property_type">
        </div>

        <div class="form-group">
            <label for="source">Source (for source-based rules)</label>
            <input type="text" id="source" name="source">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Rule</button>
            <a href="/routing-rules" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
