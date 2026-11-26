<?php $editing = $restaurant !== null; ?>
<section class="card narrow">
    <h1><?= $editing ? 'Edit restaurant' : 'Add restaurant' ?></h1>
    <form method="post" action="<?= e($editing ? url('restaurants/edit', ['id' => $restaurant['restaurant_id']]) : url('restaurants/create')) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= old('name', $restaurant['name'] ?? '') ?>" required maxlength="100">
        <?= field_error($errors, 'name') ?>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" value="<?= old('location', $restaurant['location'] ?? '') ?>" required maxlength="150">
        <?= field_error($errors, 'location') ?>

        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="<?= old('phone', $restaurant['phone'] ?? '') ?>" required maxlength="20" pattern="[0-9+()\- ]{7,20}">
        <?= field_error($errors, 'phone') ?>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn btn-light" href="<?= e(url('restaurants/index')) ?>">Cancel</a>
        </div>
    </form>
</section>
