<?php
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

// Whitelist of routes: route name => [controller class, allowed actions]. Anything else is a 404.
$routes = [
    'home'        => [HomeController::class,       ['index']],
    'auth'        => [AuthController::class,       ['login', 'register', 'logout', 'changePassword']],
    'restaurants' => [RestaurantController::class, ['index', 'show', 'create', 'edit', 'delete']],
    'menu'        => [MenuController::class,       ['create', 'edit', 'delete', 'uploadPhotoAction']],
    'orders'      => [OrderController::class,      ['index', 'show', 'place', 'edit', 'cancel', 'claim', 'deliver', 'delete']],
    'users'       => [UserController::class,       ['index', 'create', 'edit', 'delete']],
];

$route = isset($_GET['r']) && is_string($_GET['r']) ? $_GET['r'] : 'home/index';
$parts = explode('/', $route, 2);
$controller = $parts[0];
$action = $parts[1] ?? 'index';

// First-time administrator accounts must set a private password before using the system.
if (Auth::check() && !empty(Auth::user()['must_change_password']) && !in_array($controller . '/' . $action, ['auth/changePassword', 'auth/logout'], true)) {
    redirect('auth/changePassword');
}

if (!isset($routes[$controller]) || !in_array($action, $routes[$controller][1], true)) {
    notFound();
}

try {
    $class = $routes[$controller][0];
    (new $class())->$action();
} catch (Throwable $e) {
    error_log($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    view('errors/500', [
        'title'  => 'Server error',
        'detail' => APP_DEBUG ? $e->getMessage() : null,
    ]);
}
