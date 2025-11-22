<?php
// FILE: /app/views/zones/index.php
$pageTitle = 'Zones';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Zones & Territories</h1>
    <a href="/zones/create" class="btn btn-primary">Add New Zone</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Country</th>
            <th>City</th>
            <th>Area</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($zones as $zone): ?>
        <tr>
            <td><a href="/zones/<?php echo $zone['id']; ?>"><?php echo View::escape($zone['name']); ?></a></td>
            <td><?php echo View::escape($zone['country']); ?></td>
            <td><?php echo View::escape($zone['city']); ?></td>
            <td><?php echo View::escape($zone['subarea']); ?></td>
            <td><span class="badge badge-<?php echo $zone['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $zone['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
            <td>
                <a href="/zones/<?php echo $zone['id']; ?>" class="btn btn-sm">View</a>
                <a href="/zones/<?php echo $zone['id']; ?>/edit" class="btn btn-sm">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
