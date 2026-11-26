<section class="card narrow center">
    <h1>Something went wrong</h1>
    <p class="muted">The server hit an unexpected error. If you just installed the project, check the database credentials in config/config.php and that database/schema.sql and seed.sql were imported.</p>
    <?php if (!empty($detail)): ?><pre class="debug"><?= e($detail) ?></pre><?php endif; ?>
    <a class="btn" href="<?= e(url('home/index')) ?>">Back to home</a>
</section>
