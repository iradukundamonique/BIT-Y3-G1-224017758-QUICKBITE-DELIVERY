<section class="card narrow">
    <h1>Change your password</h1>
    <p class="muted">For security, you must replace the temporary administrator password before using QuickBite.</p>
    <?php if (isset($errors['form'])): ?><div class="flash flash-error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('auth/changePassword')) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        <?= field_error($errors, 'current_password') ?>
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
        <small class="muted">At least 8 characters, including a letter and a number.</small>
        <?= field_error($errors, 'new_password') ?>
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
        <?= field_error($errors, 'confirm_password') ?>
        <button type="submit" class="btn btn-block">Save new password</button>
    </form>
</section>
