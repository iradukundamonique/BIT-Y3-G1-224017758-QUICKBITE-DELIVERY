<?php
$editing = $item !== null;
$availableChecked = is_post() ? isset($_POST['available']) : ($editing ? (bool)$item['available'] : true);
?>
<section class="card narrow">
    <h1><?= $editing ? 'Edit menu item' : 'Add menu item' ?></h1>
    <p class="muted">Restaurant: <strong><?= e($restaurant['name']) ?></strong></p>
    <form method="post" enctype="multipart/form-data" action="<?= e($editing ? url('menu/edit', ['id' => $item['item_id']]) : url('menu/create', ['restaurant_id' => $restaurant['restaurant_id']])) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="name">Item name</label>
        <input type="text" id="name" name="name" value="<?= old('name', $item['name'] ?? '') ?>" required maxlength="100">
        <?= field_error($errors, 'name') ?>

        <label for="price">Price (<?= e(CURRENCY) ?>)</label>
        <input type="number" id="price" name="price" value="<?= old('price', $item['price'] ?? '') ?>" required min="1" max="99999999" step="1">
        <?= field_error($errors, 'price') ?>

        <label for="photo">Food photo (JPG, PNG or WEBP, max 4 MB)</label>
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small class="muted">Choose a picture from your computer. After selecting it, press Save to upload it.</small>
        <?= field_error($errors, 'photo') ?>
        <p id="photo-selection" class="muted" aria-live="polite">No new photo selected.</p>
        <?php if ($editing && !empty($item['photo'])): ?>
            <p class="muted">Current photo:</p><img class="menu-photo-preview" src="<?= e(BASE_URL . '/assets/uploads/menu/' . rawurlencode($item['photo'])) ?>" alt="Current <?= e($item['name']) ?> photo">
        <?php endif; ?>

        <label class="checkbox"><input type="checkbox" name="available" value="1" <?= $availableChecked ? 'checked' : '' ?>> Available to order</label>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn btn-light" href="<?= e(url('restaurants/show', ['id' => $restaurant['restaurant_id']])) ?>">Cancel</a>
        </div>
    </form>
</section>
