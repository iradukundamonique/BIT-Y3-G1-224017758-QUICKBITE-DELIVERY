<?php
declare(strict_types=1);

/** Escape for HTML output (XSS protection). */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $route = 'home/index', array $params = []): string
{
    return BASE_URL . '/index.php?' . http_build_query(['r' => $route] + $params);
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
}

function flashes(): array
{
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Trimmed POST string (arrays and missing values become ''). */
function input(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

/** Trimmed GET integer id. */
function get_id(string $key = 'id'): int
{
    $v = $_GET[$key] ?? '';
    return is_string($v) && ctype_digit($v) ? (int)$v : 0;
}

/** Escaped value to refill a form field. */
function old(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return e(is_string($v) ? $v : $default);
}

function money($amount): string
{
    return CURRENCY . number_format((float)$amount, 0);
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field])
        ? '<span class="field-error">' . e($errors[$field]) . '</span>'
        : '';
}

function status_label(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

/** A tiny POST form with a CSRF token (used for delete / cancel / etc.). */
function post_button(string $route, array $params, string $label, string $class = 'btn', ?string $confirm = null): string
{
    return '<form method="post" action="' . e(url($route, $params)) . '" class="inline-form"'
        . ($confirm !== null ? ' data-confirm="' . e($confirm) . '"' : '') . '>'
        . Csrf::field()
        . '<button type="submit" class="' . e($class) . '">' . e($label) . '</button></form>';
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/views/layout/header.php';
    require BASE_PATH . '/views/' . $name . '.php';
    require BASE_PATH . '/views/layout/footer.php';
}

function notFound(): never
{
    http_response_code(404);
    view('errors/404', ['title' => 'Not found']);
    exit;
}
