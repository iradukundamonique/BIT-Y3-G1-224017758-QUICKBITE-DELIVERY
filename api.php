<?php
declare(strict_types=1);

/**
 * JSON API - live search.
 *   GET api.php?q=pizza
 * Response: { "query": "pizza", "restaurants": [...], "items": [...] }
 */
require __DIR__ . '/core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    json_out(['error' => 'Method not allowed'], 405);
}

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
if (mb_strlen($q) > 60) {
    json_out(['error' => 'Query too long (max 60 characters)'], 422);
}
if (mb_strlen($q) < 2) {
    json_out(['query' => $q, 'restaurants' => [], 'items' => []]);
}

try {
    $restaurants = array_slice(RestaurantModel::all($q), 0, 20);
    $items = MenuItemModel::search($q, 20);
} catch (Throwable $e) {
    error_log($e->getMessage());
    json_out(['error' => 'Search is temporarily unavailable'], 500);
}

json_out(['query' => $q, 'restaurants' => $restaurants, 'items' => $items]);
