<?php
$editing = $user !== null;
$currentRole = is_post() ? input('role') : ($user['role'] ?? 'customer');
?>
<section class="card narrow">
    <h1><?= $editing ? 'Edit user' : 'Add user' ?></h1>
    <form method="post" action="<?= e($editing ? url('users/edit', ['id' => $user['user_id']]) : url('users/create')) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= old('username', $user['username'] ?? '') ?>" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_. \-]+">
        <?= field_error($errors, 'username') ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= old('email', $user['email'] ?? '') ?>" required maxlength="150">
        <?= field_error($errors, 'email') ?>

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <?php foreach (['admin', 'customer', 'rider'] as $r): ?>
                <option value="<?= e($r) ?>" <?= $currentRole === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'role') ?>

        <label for="password">Password<?= $editing ? ' (leave blank to keep current)' : '' ?></label>
        <input type="password" id="password" name="password" <?= $editing ? '' : 'required' ?> minlength="8" autocomplete="new-password" data-password>
        <?= field_error($errors, 'password') ?>

        <div class="actions">
            <button type="submit" class="btn">Save</button>
            <a class="btn btn-light" href="<?= e(url('users/index')) ?>">Cancel</a>
        </div>
    </form>
</section>
