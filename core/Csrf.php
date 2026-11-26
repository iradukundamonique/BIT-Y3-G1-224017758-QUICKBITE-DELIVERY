<?php
declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    /** Call at the top of every POST handler. */
    public static function verify(): void
    {
        $sent = $_POST['csrf_token'] ?? '';
        $real = $_SESSION['csrf_token'] ?? '';
        if (!is_string($sent) || $real === '' || !hash_equals($real, $sent)) {
            http_response_code(419);
            view('errors/419', ['title' => 'Session expired']);
            exit;
        }
    }
}
