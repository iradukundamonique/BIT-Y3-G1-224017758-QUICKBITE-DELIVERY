<section class="card narrow">
    <h1>Create your account</h1>
    <form method="post" action="<?= e(url('auth/register')) ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= old('username') ?>" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_. \-]+" autocomplete="username">
        <?= field_error($errors, 'username') ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email">
        <?= field_error($errors, 'email') ?>

        <label for="role">I am a</label>
        <select id="role" name="role" required>
            <option value="customer" <?= old('role', 'customer') === 'customer' ? 'selected' : '' ?>>Customer (order food)</option>
            <option value="rider" <?= old('role') === 'rider' ? 'selected' : '' ?>>Rider (deliver orders)</option>
        </select>
        <?= field_error($errors, 'role') ?>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" data-password>
        <small class="muted">At least 8 characters, with a letter and a number.</small>
        <?= field_error($errors, 'password') ?>

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required autocomplete="new-password" data-match="password">
        <?= field_error($errors, 'confirm') ?>

        <button type="submit" class="btn btn-block">Sign up</button>
    </form>
    <p class="muted">Already registered? <a href="<?= e(url('auth/login')) ?>">Log in</a>.</p>
</section>
