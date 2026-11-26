<section class="card narrow">
    <h1>Log in</h1>
    <?php if (isset($errors['form'])): ?>
        <div class="flash flash-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('auth/login')) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email">
        <?= field_error($errors, 'email') ?>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <?= field_error($errors, 'password') ?>

        <button type="submit" class="btn btn-block">Log in</button>
    </form>
    <p class="muted">No account? <a href="<?= e(url('auth/register')) ?>">Create one</a>.</p>
</section>
