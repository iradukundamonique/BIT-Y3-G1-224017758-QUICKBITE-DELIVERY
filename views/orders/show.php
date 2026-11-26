<?php $role = Auth::user()['role']; ?>
<div class="page-head">
    <h1>Order #<?= (int)$order['order_id'] ?></h1>
    <span class="badge status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span>
</div>

<div class="card">
    <dl class="details">
        <dt>Restaurant</dt><dd><?= e($order['restaurant_name']) ?></dd>
        <dt>Customer</dt><dd><?= e($order['customer_name']) ?></dd>
        <dt>Rider</dt><dd><?= e($order['rider_name'] ?? 'Not assigned yet') ?></dd>
        <dt>Deliver to</dt><dd><?= e($order['delivery_address']) ?></dd>
        <dt>Placed</dt><dd><?= e((string)$order['created_at']) ?></dd>
    </dl>
</div>

<div class="table-wrap">
<table>
    <thead><tr><th>Item</th><th class="num">Qty</th><th class="num">Unit price</th><th class="num">Line total</th></tr></thead>
    <tbody>
    <?php foreach ($items as $it): ?>
        <tr>
            <td><?= e($it['name']) ?></td>
            <td class="num"><?= (int)$it['quantity'] ?></td>
            <td class="num"><?= e(money($it['unit_price'])) ?></td>
            <td class="num"><?= e(money($it['line_total'])) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3" class="num">Total</th><th class="num"><?= e(money($order['total'])) ?></th></tr></tfoot>
</table>
</div>

<div class="actions">
    <a class="btn btn-light" href="<?= e(url('orders/index')) ?>">← Back to orders</a>
    <?php if ($role === 'admin'): ?>
        <a class="btn" href="<?= e(url('orders/edit', ['id' => $order['order_id']])) ?>">Update status / rider</a>
    <?php endif; ?>
    <?php if ($role === 'customer' && $order['status'] === 'pending'): ?>
        <?= post_button('orders/cancel', ['id' => $order['order_id']], 'Cancel order', 'btn btn-danger', 'Cancel this order?') ?>
    <?php endif; ?>
    <?php if ($role === 'rider' && $order['rider_id'] === null && $order['status'] === 'ready'): ?>
        <?= post_button('orders/claim', ['id' => $order['order_id']], 'Claim this order', 'btn') ?>
    <?php endif; ?>
    <?php if ($role === 'rider' && (int)$order['rider_id'] === Auth::id() && $order['status'] === 'out_for_delivery'): ?>
        <?= post_button('orders/deliver', ['id' => $order['order_id']], 'Mark delivered', 'btn', 'Confirm this order was delivered?') ?>
    <?php endif; ?>
</div>
