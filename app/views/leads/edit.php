<?php
// FILE: /app/views/leads/edit.php
$pageTitle = 'Edit Lead';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Edit Lead</h1>
    <a href="/leads/<?php echo $lead['id']; ?>" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/leads/<?php echo $lead['id']; ?>/update" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="<?php echo View::escape($lead['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone *</label>
                <input type="tel" id="phone" name="phone" value="<?php echo View::escape($lead['phone']); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo View::escape($lead['email']); ?>">
            </div>

            <div class="form-group">
                <label for="source">Source</label>
                <input type="text" id="source" name="source" value="<?php echo View::escape($lead['source']); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="property_type">Property Type</label>
                <select id="property_type" name="property_type">
                    <option value="">Select Type</option>
                    <option value="Apartment" <?php echo $lead['property_type'] === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="Villa" <?php echo $lead['property_type'] === 'Villa' ? 'selected' : ''; ?>>Villa</option>
                    <option value="Townhouse" <?php echo $lead['property_type'] === 'Townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                    <option value="Penthouse" <?php echo $lead['property_type'] === 'Penthouse' ? 'selected' : ''; ?>>Penthouse</option>
                </select>
            </div>

            <div class="form-group">
                <label for="campaign">Campaign</label>
                <input type="text" id="campaign" name="campaign" value="<?php echo View::escape($lead['campaign']); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="budget_min">Budget Min</label>
                <input type="number" id="budget_min" name="budget_min" value="<?php echo $lead['budget_min']; ?>">
            </div>

            <div class="form-group">
                <label for="budget_max">Budget Max</label>
                <input type="number" id="budget_max" name="budget_max" value="<?php echo $lead['budget_max']; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="preferred_city">City</label>
                <input type="text" id="preferred_city" name="preferred_city" value="<?php echo View::escape($lead['preferred_city']); ?>">
            </div>

            <div class="form-group">
                <label for="preferred_area">Area</label>
                <input type="text" id="preferred_area" name="preferred_area" value="<?php echo View::escape($lead['preferred_area']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?php echo View::escape($lead['notes']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Lead</button>
            <a href="/leads/<?php echo $lead['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
