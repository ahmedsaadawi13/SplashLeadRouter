<?php
// FILE: /app/views/zones/edit.php
$pageTitle = 'Edit Zone';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Edit Zone</h1>
    <a href="/zones/<?php echo $zone['id']; ?>" class="btn btn-secondary">Back</a>
</div>

<div class="form-container">
    <form method="POST" action="/zones/<?php echo $zone['id']; ?>/update" class="form">
        <?php echo View::csrfField(); ?>

        <div class="form-group">
            <label for="name">Zone Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape($zone['name']); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?php echo View::escape($zone['country']); ?>">
            </div>

            <div class="form-group">
                <label for="region">Region</label>
                <input type="text" id="region" name="region" value="<?php echo View::escape($zone['region']); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo View::escape($zone['city']); ?>">
            </div>

            <div class="form-group">
                <label for="subarea">Sub-Area</label>
                <input type="text" id="subarea" name="subarea" value="<?php echo View::escape($zone['subarea']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="custom_label">Custom Label</label>
            <input type="text" id="custom_label" name="custom_label" value="<?php echo View::escape($zone['custom_label']); ?>">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?php echo $zone['is_active'] ? 'checked' : ''; ?>>
                Active
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Zone</button>
            <a href="/zones/<?php echo $zone['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
