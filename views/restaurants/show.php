<?php
$isAdmin = Auth::hasRole('admin');
$isCustomer = Auth::hasRole('customer');
?>
<div class="page-head">
    <div>
        <h1><?= e($restaurant['name']) ?></h1>
        <p class="muted">📍 <?= e($restaurant['location']) ?> · 📞 <?= e($restaurant['phone']) ?></p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="actions">
            <a class="btn btn-light" href="<?= e(url('restaurants/edit', ['id' => $restaurant['restaurant_id']])) ?>">Edit restaurant</a>
            <a class="btn" href="<?= e(url('menu/create', ['restaurant_id' => $restaurant['restaurant_id']])) ?>">+ Add menu item</a>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($errors['qty'])): ?>
    <div class="flash flash-error" role="alert"><?= e($errors['qty']) ?></div>
<?php endif; ?>

<?php if (!$items): ?>
    <p class="muted">This restaurant has no menu items yet.</p>
<?php else: ?>
<?php if ($isCustomer): ?>
<form method="post" action="<?= e(url('orders/place')) ?>" data-validate novalidate id="order-form">
    <?= Csrf::field() ?>
    <input type="hidden" name="restaurant_id" value="<?= (int)$restaurant['restaurant_id'] ?>">
<?php endif; ?>

<div class="table-wrap">
<table>
    <thead>
        <tr>
            <th>Food</th><th class="num">Price</th>
            <?php if ($isAdmin): ?><th>Status</th><th></th><?php elseif ($isCustomer): ?><th class="num">Qty</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $it): ?>
        <tr>
            <td class="menu-item-cell"><?php if (!empty($it['photo'])): ?><img class="menu-thumb" src="<?= e(BASE_URL . '/assets/uploads/menu/' . rawurlencode($it['photo'])) ?>" alt="<?= e($it['name']) ?>"><?php else: ?><span class="menu-thumb-placeholder">Photo placeholder</span><?php endif; ?><strong><?= e($it['name']) ?></strong></td>
            <td class="num"><?= e(money($it['price'])) ?></td>
            <?php if ($isAdmin): ?>
                <td><span class="badge <?= $it['available'] ? 'badge-ok' : 'badge-off' ?>"><?= $it['available'] ? 'Available' : 'Unavailable' ?></span></td>
                <td class="actions">
                    <form method="post" enctype="multipart/form-data" action="<?= e(url('menu/uploadPhotoAction', ['id' => $it['item_id']])) ?>" class="admin-photo-upload">
                        <?= Csrf::field() ?>
                        <label class="btn btn-small btn-light" for="food-photo-<?= (int)$it['item_id'] ?>">Choose photo</label>
                        <input type="file" id="food-photo-<?= (int)$it['item_id'] ?>" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                        <button type="submit" class="btn btn-small">Upload photo</button>
                    </form>
                    <a class="btn btn-small btn-light" href="<?= e(url('menu/edit', ['id' => $it['item_id']])) ?>">Edit</a>
                    <?= post_button('menu/delete', ['id' => $it['item_id']], 'Delete', 'btn btn-small btn-danger', 'Delete this menu item?') ?>
                </td>
            <?php elseif ($isCustomer): ?>
                <td class="num">
                    <input type="number" class="qty-input" name="qty[<?= (int)$it['item_id'] ?>]" value="0" min="0" max="50"
                           data-price="<?= e((string)$it['price']) ?>" aria-label="Quantity of <?= e($it['name']) ?>">
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php if ($isCustomer): ?>
    <div class="card order-box">
        <label for="delivery_address">Delivery address</label>
        <input type="text" id="delivery_address" name="delivery_address" value="<?= old('delivery_address') ?>" required minlength="5" maxlength="255" placeholder="Street, building, apartment">
        <?= field_error($errors, 'delivery_address') ?>
        <p class="total-line">Total: <strong id="order-total" data-currency="<?= e(CURRENCY) ?>"><?= e(money(0)) ?></strong></p>
        <button type="submit" class="btn">Place order</button>
    </div>
</form>
<?php elseif (!Auth::check()): ?>
    <p class="muted"><a href="<?= e(url('auth/login')) ?>">Log in</a> as a customer to place an order.</p>
<?php elseif (!$isAdmin): ?>
    <p class="muted">Only customer accounts can place orders.</p>
<?php endif; ?>
<?php endif; ?>
