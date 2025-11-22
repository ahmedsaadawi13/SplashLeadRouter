<?php
// FILE: /app/views/agents/index.php
$pageTitle = 'Agents';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Agents</h1>
    <a href="/agents/create" class="btn btn-primary">Add New Agent</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Level</th>
            <th>Capacity</th>
            <th>Active Leads</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($agents as $agent): ?>
        <?php
        $capacity = null;
        foreach ($agents_capacity as $ac) {
            if ($ac['id'] == $agent['id']) {
                $capacity = $ac;
                break;
            }
        }
        ?>
        <tr>
            <td><a href="/agents/<?php echo $agent['id']; ?>"><?php echo View::escape($agent['name']); ?></a></td>
            <td><?php echo View::escape($agent['email']); ?></td>
            <td><?php echo View::escape($agent['phone']); ?></td>
            <td><?php echo View::escape($agent['agent_level']); ?></td>
            <td><?php echo $agent['max_active_leads']; ?></td>
            <td><?php echo $capacity ? $capacity['current_active_leads'] : 0; ?></td>
            <td><span class="badge badge-<?php echo $agent['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $agent['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
            <td>
                <a href="/agents/<?php echo $agent['id']; ?>" class="btn btn-sm">View</a>
                <a href="/agents/<?php echo $agent['id']; ?>/edit" class="btn btn-sm">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
