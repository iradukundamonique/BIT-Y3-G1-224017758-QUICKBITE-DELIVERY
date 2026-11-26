<section class="hero">
<h1>fast delivery.</h1>
    <em><h1>Browse restaurants and delicious meals.<br> welcome to QuickBite!</h1></em>
    <div class="search-box">
        <label for="live-search" class="sr-only">Search restaurants and food</label>
        <input type="search" id="live-search" placeholder="Search a restaurant or a dish, e.g. pizza" autocomplete="off" maxlength="60">
    </div>
    <div id="search-results" class="search-results" aria-live="polite" hidden></div>
</section>

<div class="menu-section-heading" id="our-food-menu">
    <a class="btn menu-jump-button" href="#food-menu-items"><marquee behavior="blink" direction="left"><h1>OUR FOOD MENU</h1></marquee></a>

    <a class="btn menu-jump-button" href="#food-menu-items">View our food menu</a>
</div>
<div id="food-menu-items">
<?php if (!$menuItems): ?>
    <p class="muted">No food has been added yet. Our menu will appear here when the admin adds dishes.</p>
<?php else: ?>
<div class="food-grid">
    <?php foreach ($menuItems as $food): ?>
        <article class="card food-card">
            <?php if (!empty($food['photo'])): ?>
                <img class="food-photo" src="<?= e(BASE_URL . '/assets/uploads/menu/' . rawurlencode($food['photo'])) ?>" alt="<?= e($food['name']) ?>">
            <?php else: ?>
                <div class="food-photo-placeholder" role="img" aria-label="Food photo placeholder">Food photo will appear here</div>
            <?php endif; ?>
            <div class="food-card-content">
                <h2><?= e($food['name']) ?></h2>
                <h2 class="muted"><?= e($food['restaurant_name']) ?></h2>
                <strong><?= e(money($food['price'])) ?></strong>
                <p><a class="btn btn-small" href="<?= e(url('restaurants/show', ['id' => $food['restaurant_id']])) ?>">View restaurant</a></p>
            </div>
        </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<h2> Find Restaurants near you</h2>
<?php if (!$restaurants): ?><p class="muted">No restaurants yet.</p><?php endif; ?>
<div class="grid">
    <?php foreach ($restaurants as $r): ?>
        <a class="card tile" href="<?= e(url('restaurants/show', ['id' => $r['restaurant_id']])) ?>">
            <h3><?= e($r['name']) ?></h3>
            <p class="muted">📍 <?= e($r['location']) ?></p>
            <p class="muted">📞 <?= e($r['phone']) ?></p>
        </a>
    <?php endforeach; ?>
</div>
