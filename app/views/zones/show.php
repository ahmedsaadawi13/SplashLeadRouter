<?php
// FILE: /app/views/zones/show.php
$pageTitle = 'Zone Details';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <h1>Zone: <?php echo View::escape($zone['name']); ?></h1>
    <div>
        <a href="/zones/<?php echo $zone['id']; ?>/edit" class="btn">Edit</a>
        <a href="/zones" class="btn btn-secondary">Back</a>
    </div>
</div>

<div class="zone-info">
    <h2>Zone Information</h2>
    <dl>
        <dt>Country:</dt>
        <dd><?php echo View::escape($zone['country']); ?></dd>
        <dt>Region:</dt>
        <dd><?php echo View::escape($zone['region']); ?></dd>
        <dt>City:</dt>
        <dd><?php echo View::escape($zone['city']); ?></dd>
        <dt>Sub-Area:</dt>
        <dd><?php echo View::escape($zone['subarea']); ?></dd>
        <dt>Status:</dt>
        <dd><?php echo $zone['is_active'] ? 'Active' : 'Inactive'; ?></dd>
    </dl>
</div>

<div class="assigned-agents">
    <h2>Assigned Agents</h2>

    <?php if (!empty($available_agents)): ?>
    <form method="POST" action="/zones/<?php echo $zone['id']; ?>/assign-agent" class="inline-form">
        <?php echo View::csrfField(); ?>
        <select name="agent_id" required>
            <option value="">Select Agent</option>
            <?php foreach ($available_agents as $agent): ?>
            <option value="<?php echo $agent['id']; ?>"><?php echo View::escape($agent['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="priority" value="1" min="1" placeholder="Priority">
        <input type="number" name="weight" value="1" min="1" placeholder="Weight">
        <label><input type="checkbox" name="is_primary"> Primary</label>
        <button type="submit" class="btn btn-primary">Assign Agent</button>
    </form>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Agent</th>
                <th>Priority</th>
                <th>Weight</th>
                <th>Primary</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($zone['agents'])): ?>
            <tr>
                <td colspan="5">No agents assigned yet</td>
            </tr>
            <?php else: ?>
            <?php foreach ($zone['agents'] as $agent): ?>
            <tr>
                <td><?php echo View::escape($agent['name']); ?></td>
                <td><?php echo $agent['priority']; ?></td>
                <td><?php echo $agent['weight']; ?></td>
                <td><?php echo $agent['is_primary'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="POST" action="/zones/<?php echo $zone['id']; ?>/remove-agent/<?php echo $agent['id']; ?>" style="display:inline;">
                        <?php echo View::csrfField(); ?>
                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
