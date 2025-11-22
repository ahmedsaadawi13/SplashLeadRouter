<?php
// FILE: /app/views/leads/create.php
$pageTitle = 'Create Lead';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Create New Lead</h1>
    <a href="/leads" class="btn btn-secondary">Back to Leads</a>
</div>

<div class="form-container">
    <form method="POST" action="/leads" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="<?php echo View::escape(View::old('name')); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone *</label>
                <input type="tel" id="phone" name="phone" value="<?php echo View::escape(View::old('phone')); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo View::escape(View::old('email')); ?>">
            </div>

            <div class="form-group">
                <label for="source">Source</label>
                <select id="source" name="source">
                    <option value="manual">Manual Entry</option>
                    <?php foreach ($sources as $source): ?>
                    <option value="<?php echo View::escape($source['name']); ?>"><?php echo View::escape($source['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="property_type">Property Type</label>
                <select id="property_type" name="property_type">
                    <option value="">Select Type</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Villa">Villa</option>
                    <option value="Townhouse">Townhouse</option>
                    <option value="Penthouse">Penthouse</option>
                    <option value="Studio">Studio</option>
                </select>
            </div>

            <div class="form-group">
                <label for="campaign">Campaign</label>
                <input type="text" id="campaign" name="campaign" value="<?php echo View::escape(View::old('campaign')); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="budget_min">Budget Min</label>
                <input type="number" id="budget_min" name="budget_min" value="<?php echo View::escape(View::old('budget_min')); ?>">
            </div>

            <div class="form-group">
                <label for="budget_max">Budget Max</label>
                <input type="number" id="budget_max" name="budget_max" value="<?php echo View::escape(View::old('budget_max')); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="preferred_city">City</label>
                <input type="text" id="preferred_city" name="preferred_city" value="<?php echo View::escape(View::old('preferred_city')); ?>">
            </div>

            <div class="form-group">
                <label for="preferred_area">Area</label>
                <input type="text" id="preferred_area" name="preferred_area" value="<?php echo View::escape(View::old('preferred_area')); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?php echo View::escape(View::old('notes')); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Lead</button>
            <a href="/leads" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
