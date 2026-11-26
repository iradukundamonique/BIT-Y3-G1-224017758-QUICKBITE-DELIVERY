<section class="card narrow">
    <h1>Update order #<?= (int)$order['order_id'] ?></h1>
    <p class="muted"><?= e($order['restaurant_name']) ?> · <?= e($order['customer_name']) ?> · <?= e(money($order['total'])) ?></p>
    <form method="post" action="<?= e(url('orders/edit', ['id' => $order['order_id']])) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <?php $currentStatus = is_post() ? input('status') : $order['status']; ?>
            <?php foreach (OrderModel::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $currentStatus === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'status') ?>

        <label for="rider_id">Rider</label>
        <select id="rider_id" name="rider_id">
            <?php $currentRider = is_post() ? input('rider_id') : (string)($order['rider_id'] ?? ''); ?>
            <option value="">— Not assigned —</option>
            <?php foreach ($riders as $r): ?>
                <option value="<?= (int)$r['user_id'] ?>" <?= $currentRider === (string)$r['user_id'] ? 'selected' : '' ?>><?= e($r['username']) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'rider_id') ?>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn btn-light" href="<?= e(url('orders/show', ['id' => $order['order_id']])) ?>">Cancel</a>
        </div>
    </form>
</section>
