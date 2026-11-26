<div class="page-head">
    <h1>Restaurants</h1>
    <?php if (Auth::hasRole('admin')): ?>
        <a class="btn" href="<?= e(url('restaurants/create')) ?>">+ Add restaurant</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="inline-search">
    <input type="hidden" name="r" value="restaurants/index">
    <label for="q" class="sr-only">Search</label>
    <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search by name or location" maxlength="60">
    <button type="submit" class="btn">Search</button>
</form>

<?php if (!$restaurants): ?>
    <p class="muted">No restaurants found.</p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th>Name</th><th>Location</th><th>Phone</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($restaurants as $r): ?>
        <tr>
            <td><a href="<?= e(url('restaurants/show', ['id' => $r['restaurant_id']])) ?>"><?= e($r['name']) ?></a></td>
            <td><?= e($r['location']) ?></td>
            <td><?= e($r['phone']) ?></td>
            <td class="actions">
                <a class="btn btn-small" href="<?= e(url('restaurants/show', ['id' => $r['restaurant_id']])) ?>">View menu</a>
                <?php if (Auth::hasRole('admin')): ?>
                    <a class="btn btn-small btn-light" href="<?= e(url('restaurants/edit', ['id' => $r['restaurant_id']])) ?>">Edit</a>
                    <?= post_button('restaurants/delete', ['id' => $r['restaurant_id']], 'Delete', 'btn btn-small btn-danger', 'Delete this restaurant and its menu?') ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
