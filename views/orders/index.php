<?php $role = Auth::user()['role']; ?>
<div class="page-head">
    <h1><?= e($title) ?></h1>
</div>
<?php if ($role === 'rider'): ?>
    <p class="muted">Your deliveries plus any <strong>Ready</strong> orders waiting for a rider.</p>
<?php endif; ?>

<?php if (!$orders): ?>
    <p class="muted">No orders to show.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>#</th><th>Restaurant</th>
            <?php if ($role !== 'customer'): ?><th>Customer</th><?php endif; ?>
            <?php if ($role !== 'rider'): ?><th>Rider</th><?php endif; ?>
            <th class="num">Total</th><th>Status</th><th>Placed</th><th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><a href="<?= e(url('orders/show', ['id' => $o['order_id']])) ?>">#<?= (int)$o['order_id'] ?></a></td>
            <td><?= e($o['restaurant_name']) ?></td>
            <?php if ($role !== 'customer'): ?><td><?= e($o['customer_name']) ?></td><?php endif; ?>
            <?php if ($role !== 'rider'): ?><td><?= e($o['rider_name'] ?? '—') ?></td><?php endif; ?>
            <td class="num"><?= e(money($o['total'])) ?></td>
            <td><span class="badge status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
            <td><?= e(substr((string)$o['created_at'], 0, 16)) ?></td>
            <td class="actions">
                <a class="btn btn-small btn-light" href="<?= e(url('orders/show', ['id' => $o['order_id']])) ?>">Details</a>
                <?php if ($role === 'customer' && $o['status'] === 'pending'): ?>
                    <?= post_button('orders/cancel', ['id' => $o['order_id']], 'Cancel', 'btn btn-small btn-danger', 'Cancel this order?') ?>
                <?php endif; ?>
                <?php if ($role === 'rider' && $o['rider_id'] === null && $o['status'] === 'ready'): ?>
                    <?= post_button('orders/claim', ['id' => $o['order_id']], 'Claim', 'btn btn-small') ?>
                <?php endif; ?>
                <?php if ($role === 'rider' && (int)$o['rider_id'] === Auth::id() && $o['status'] === 'out_for_delivery'): ?>
                    <?= post_button('orders/deliver', ['id' => $o['order_id']], 'Mark delivered', 'btn btn-small', 'Confirm this order was delivered?') ?>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a class="btn btn-small" href="<?= e(url('orders/edit', ['id' => $o['order_id']])) ?>">Update</a>
                    <?= post_button('orders/delete', ['id' => $o['order_id']], 'Delete', 'btn btn-small btn-danger', 'Permanently delete this order?') ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
