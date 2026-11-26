<?php
declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    public static function hasRole(string ...$roles): bool
    {
        return self::check() && in_array($_SESSION['user']['role'], $roles, true);
    }

    public static function login(array $row): void
    {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user'] = [
            'id'       => (int)$row['user_id'],
            'username' => $row['username'],
            'role'     => $row['role'],
            'must_change_password' => (int)($row['must_change_password'] ?? 0),
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    /** Re-check the logged-in user against the DB on every request. */
    public static function refresh(): void
    {
        if (!self::check()) {
            return;
        }
        try {
            $row = UserModel::find(self::id());
        } catch (Throwable $e) {
            return; // DB down: the page itself will report it
        }
        if (!$row) {
            self::logout();
            return;
        }
        $_SESSION['user']['role'] = $row['role'];
        $_SESSION['user']['username'] = $row['username'];
        $_SESSION['user']['must_change_password'] = (int)($row['must_change_password'] ?? 0);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please log in to continue.');
            redirect('auth/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            view('errors/403', ['title' => 'Forbidden']);
            exit;
        }
    }

    /** Where to send someone right after logging in. */
    public static function homeRoute(): string
    {
        return self::hasRole('admin', 'rider') ? 'orders/index' : 'home/index';
    }
}
