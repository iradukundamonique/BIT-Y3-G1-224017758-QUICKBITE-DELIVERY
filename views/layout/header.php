<?php
$currentUser = Auth::user();
$pageTitle = ($title ?? 'Home') . ' · ' . APP_NAME;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/style.css">
</head>
<body data-api="<?= e(BASE_URL . '/api.php') ?>" data-show-url="<?= e(url('restaurants/show')) ?>">
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= e(url('home/index')) ?>"> QUICKBITE ONLINE FOOD DELIVERY</a>
        <button type="button" class="nav-toggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="nav-links">☰</button>
        <nav id="nav-links" class="nav-links">
            <a href="<?= e(url('home/index')) ?>">Home</a>
            <a href="<?= e(url('restaurants/index')) ?>">Restaurants</a>
            <?php if ($currentUser): ?>
                <a href="<?= e(url('orders/index')) ?>"><?= $currentUser['role'] === 'customer' ? 'My orders' : 'Orders' ?></a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="<?= e(url('users/index')) ?>">Users</a>
                <?php endif; ?>
                <a href="<?= e(url('auth/changePassword')) ?>">Change password</a>
                <span class="nav-user"><?= e($currentUser['username']) ?> <small>(<?= e($currentUser['role']) ?>)</small></span>
                <form method="post" action="<?= e(url('auth/logout')) ?>" class="inline-form">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-small btn-light">Log out</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('auth/login')) ?>">Log in</a>
                <a class="btn btn-small btn-light" href="<?= e(url('auth/register')) ?>">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
    <?php foreach (flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div>
    <?php endforeach; ?>
