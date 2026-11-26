<?php
declare(strict_types=1);

/** Admin-only user management (full CRUD). */
final class UserController
{
    private const ROLES = ['admin', 'customer', 'rider'];

    public function index(): void
    {
        Auth::requireRole('admin');
        view('users/index', ['title' => 'Users', 'users' => UserModel::all()]);
    }

    public function create(): void
    {
        Auth::requireRole('admin');
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate(0, true);
            if (!$errors) {
                UserModel::create(input('username'), strtolower(input('email')), $this->password(), input('role'));
                flash('success', 'User created.');
                redirect('users/index');
            }
        }
        view('users/form', ['title' => 'Add user', 'errors' => $errors, 'user' => null]);
    }

    public function edit(): void
    {
        Auth::requireRole('admin');
        $user = UserModel::find(get_id());
        if (!$user) {
            notFound();
        }
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $errors = $this->validate((int)$user['user_id'], false);
            // An admin cannot demote themselves (avoids locking everyone out)
            if ((int)$user['user_id'] === Auth::id() && input('role') !== 'admin') {
                $errors['role'] = 'You cannot change your own role.';
            }
            if (!$errors) {
                UserModel::update((int)$user['user_id'], input('username'), strtolower(input('email')), input('role'), $this->password());
                flash('success', 'User updated.');
                redirect('users/index');
            }
        }
        view('users/form', ['title' => 'Edit user', 'errors' => $errors, 'user' => $user]);
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        if (!is_post()) {
            redirect('users/index');
        }
        Csrf::verify();
        $id = get_id();
        if ($id === Auth::id()) {
            flash('error', 'You cannot delete your own account.');
        } elseif (!UserModel::find($id)) {
            notFound();
        } elseif (UserModel::delete($id)) {
            flash('success', 'User deleted.');
        } else {
            flash('error', 'This user has orders and cannot be deleted.');
        }
        redirect('users/index');
    }

    private function password(): string
    {
        return is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    }

    private function validate(int $exceptId, bool $passwordRequired): array
    {
        $v = (new Validator($_POST))
            ->required('username', 'Username')->min('username', 'Username', 3)->max('username', 'Username', 50)
            ->pattern('username', '/^[A-Za-z0-9_. -]+$/', 'Username may contain letters, numbers, spaces and _ . -')
            ->required('email', 'Email')->email('email')->max('email', 'Email', 150)
            ->inList('role', 'role', self::ROLES)
            ->password('password');
        if ($passwordRequired) {
            $v->required('password', 'Password');
        }
        $errors = $v->errors;
        if (!isset($errors['email']) && UserModel::emailTaken(strtolower(input('email')), $exceptId)) {
            $errors['email'] = 'That email is already registered.';
        }
        return $errors;
    }
}
