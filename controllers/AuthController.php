<?php
declare(strict_types=1);

final class AuthController
{
    // Used so a wrong email takes about as long to reject as a wrong password.
    private const DUMMY_HASH = '$2y$10$.Z4u4IFo1tzrjx6SnKK7K.6xCRzOmkY/zKGZduniEZ1k8oZFzTf.i';

    public function login(): void
    {
        if (Auth::check()) {
            redirect(Auth::homeRoute());
        }
        $errors = [];

        if (is_post()) {
            Csrf::verify();
            $email = strtolower(input('email'));
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            $v = (new Validator(['email' => $email, 'password' => $password]))
                ->required('email', 'Email')->email('email')
                ->required('password', 'Password');
            $errors = $v->errors;

            if (!$errors) {
                $user = UserModel::findByEmail($email);
                $ok = password_verify($password, $user['password_hash'] ?? self::DUMMY_HASH);
                if ($user && $ok) {
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        UserModel::updatePasswordHash((int)$user['user_id'], $password);
                    }
                    Auth::login($user);
                    if (!empty($user['must_change_password'])) {
                        redirect('auth/changePassword');
                    }
                    flash('success', 'Welcome back, ' . $user['username'] . '!');
                    redirect(Auth::homeRoute());
                }
                $errors['form'] = 'Invalid email or password.';
            }
        }
        view('auth/login', ['title' => 'Log in', 'errors' => $errors]);
    }


    public function changePassword(): void
    {
        Auth::requireLogin();
        $errors = [];
        if (is_post()) {
            Csrf::verify();
            $current = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
            $password = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
            $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
            $user = UserModel::find(Auth::id());
            if (!$user || !password_verify($current, $user['password_hash'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            }
            $v = (new Validator(['new_password' => $password, 'confirm_password' => $confirm]))
                ->required('new_password', 'New password')->password('new_password')
                ->required('confirm_password', 'Password confirmation')->matches('confirm_password', 'new_password', 'Password confirmation');
            $errors = array_merge($errors, $v->errors);
            if (!$errors) {
                UserModel::updatePasswordHash(Auth::id(), $password);
                $_SESSION['user']['must_change_password'] = 0;
                flash('success', 'Your password has been changed successfully.');
                redirect(Auth::homeRoute());
            }
        }
        view('auth/change_password', ['title' => 'Change password', 'errors' => $errors]);
    }

    public function register(): void
    {
        if (Auth::check()) {
            redirect('home/index');
        }
        $errors = [];

        if (is_post()) {
            Csrf::verify();
            $data = [
                'username' => input('username'),
                'email'    => strtolower(input('email')),
                'role'     => input('role'),
                'password' => is_string($_POST['password'] ?? null) ? $_POST['password'] : '',
                'confirm'  => is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '',
            ];
            $v = (new Validator($data))
                ->required('username', 'Username')->min('username', 'Username', 3)->max('username', 'Username', 50)
                ->pattern('username', '/^[A-Za-z0-9_. -]+$/', 'Username may contain letters, numbers, spaces and _ . -')
                ->required('email', 'Email')->email('email')->max('email', 'Email', 150)
                ->required('password', 'Password')->password('password')
                ->required('confirm', 'Password confirmation')->matches('confirm', 'password', 'Password confirmation')
                ->inList('role', 'account type', ['customer', 'rider']); // admins cannot self-register
            $errors = $v->errors;

            if (!isset($errors['email']) && UserModel::emailTaken($data['email'])) {
                $errors['email'] = 'That email is already registered.';
            }

            if (!$errors) {
                UserModel::create($data['username'], $data['email'], $data['password'], $data['role']);
                flash('success', 'Account created. You can now log in.');
                redirect('auth/login');
            }
        }
        view('auth/register', ['title' => 'Create account', 'errors' => $errors]);
    }

    public function logout(): void
    {
        if (!is_post()) {
            redirect('home/index');
        }
        Csrf::verify();
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('home/index');
    }
}
