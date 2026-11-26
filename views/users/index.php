<div class="page-head">
    <h1>Users</h1>
    <a class="btn" href="<?= e(url('users/create')) ?>">+ Add user</a>
</div>
<div class="table-wrap">
<table>
    <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int)$u['user_id'] ?></td>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="badge role-<?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
            <td><?= e(substr((string)$u['created_at'], 0, 10)) ?></td>
            <td class="actions">
                <a class="btn btn-small btn-light" href="<?= e(url('users/edit', ['id' => $u['user_id']])) ?>">Edit</a>
                <?php if ((int)$u['user_id'] !== Auth::id()): ?>
                    <?= post_button('users/delete', ['id' => $u['user_id']], 'Delete', 'btn btn-small btn-danger', 'Delete this user?') ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
