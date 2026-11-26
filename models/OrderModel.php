<?php
declare(strict_types=1);

final class OrderModel
{
    public const STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled'];

    private const SELECT = 'SELECT o.*, r.name AS restaurant_name, c.username AS customer_name, d.username AS rider_name
          FROM orders o
          JOIN restaurants r ON r.restaurant_id = o.restaurant_id
          JOIN users c ON c.user_id = o.customer_id
          LEFT JOIN users d ON d.user_id = o.rider_id';

    /**
     * Create an order + its lines in one transaction.
     * Prices are read from the DB (never trusted from the browser).
     *
     * @param array<int,int> $qtyByItem item_id => quantity
     * @throws RuntimeException when an item is unavailable / from another restaurant
     */
    public static function create(int $customerId, int $restaurantId, string $address, array $qtyByItem): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $ids = array_keys($qtyByItem);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT item_id, price FROM menu_items
                  WHERE restaurant_id = ? AND available = 1 AND item_id IN ($in)"
            );
            $stmt->execute(array_merge([$restaurantId], $ids));
            $prices = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            if (count($prices) !== count($ids)) {
                throw new RuntimeException('One or more selected items are no longer available.');
            }

            $totalCents = 0;
            foreach ($qtyByItem as $itemId => $qty) {
                $totalCents += (int)round((float)$prices[$itemId] * 100) * $qty;
            }

            Database::query(
                "INSERT INTO orders (customer_id, restaurant_id, status, total, delivery_address)
                 VALUES (?, ?, 'pending', ?, ?)",
                [$customerId, $restaurantId, number_format($totalCents / 100, 2, '.', ''), $address]
            );
            $orderId = (int)$pdo->lastInsertId();

            $line = $pdo->prepare('INSERT INTO order_items (order_id, item_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            foreach ($qtyByItem as $itemId => $qty) {
                $line->execute([$orderId, $itemId, $qty, $prices[$itemId]]);
            }

            $pdo->commit();
            return $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Orders visible to this user: admin = all, customer = own, rider = own + unclaimed "ready" orders. */
    public static function listFor(string $role, int $userId): array
    {
        $sql = self::SELECT;
        $params = [];
        if ($role === 'customer') {
            $sql .= ' WHERE o.customer_id = ?';
            $params[] = $userId;
        } elseif ($role === 'rider') {
            $sql .= " WHERE o.rider_id = ? OR (o.rider_id IS NULL AND o.status = 'ready')";
            $params[] = $userId;
        }
        $sql .= ' ORDER BY o.order_id DESC';
        return Database::query($sql, $params)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::query(self::SELECT . ' WHERE o.order_id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function items(int $orderId): array
    {
        return Database::query(
            'SELECT oi.*, m.name, (oi.quantity * oi.unit_price) AS line_total
               FROM order_items oi
               JOIN menu_items m ON m.item_id = oi.item_id
              WHERE oi.order_id = ?
              ORDER BY m.name',
            [$orderId]
        )->fetchAll();
    }

    public static function canView(array $order, array $user): bool
    {
        return match ($user['role']) {
            'admin'    => true,
            'customer' => (int)$order['customer_id'] === $user['id'],
            'rider'    => (int)$order['rider_id'] === $user['id']
                          || ($order['rider_id'] === null && $order['status'] === 'ready'),
            default    => false,
        };
    }

    /** Admin update: status + optional rider. */
    public static function adminUpdate(int $id, string $status, ?int $riderId): void
    {
        Database::query('UPDATE orders SET status = ?, rider_id = ? WHERE order_id = ?', [$status, $riderId, $id]);
    }

    /** Customer may cancel only while still pending. */
    public static function cancel(int $id, int $customerId): bool
    {
        return Database::query(
            "UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND customer_id = ? AND status = 'pending'",
            [$id, $customerId]
        )->rowCount() === 1;
    }

    /** Rider takes an unclaimed ready order (atomic: only one rider can win). */
    public static function claim(int $id, int $riderId): bool
    {
        return Database::query(
            "UPDATE orders SET rider_id = ?, status = 'out_for_delivery'
              WHERE order_id = ? AND rider_id IS NULL AND status = 'ready'",
            [$riderId, $id]
        )->rowCount() === 1;
    }

    public static function markDelivered(int $id, int $riderId): bool
    {
        return Database::query(
            "UPDATE orders SET status = 'delivered'
              WHERE order_id = ? AND rider_id = ? AND status = 'out_for_delivery'",
            [$id, $riderId]
        )->rowCount() === 1;
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM orders WHERE order_id = ?', [$id]); // order_items cascade
    }
}
