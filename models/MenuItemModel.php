<?php
declare(strict_types=1);

final class MenuItemModel
{
    public static function byRestaurant(int $restaurantId, bool $onlyAvailable = false): array
    {
        $sql = 'SELECT * FROM menu_items WHERE restaurant_id = ?' . ($onlyAvailable ? ' AND available = 1' : '') . ' ORDER BY name';
        return Database::query($sql, [$restaurantId])->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM menu_items WHERE item_id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(int $restaurantId, string $name, string $price, bool $available, ?string $photo = null): int
    {
        Database::query(
            'INSERT INTO menu_items (restaurant_id, name, price, available, photo) VALUES (?, ?, ?, ?, ?)',
            [$restaurantId, $name, $price, (int)$available, $photo]
        );
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, string $name, string $price, bool $available, ?string $photo = null): void
    {
        Database::query(
            'UPDATE menu_items SET name = ?, price = ?, available = ?, photo = COALESCE(?, photo) WHERE item_id = ?',
            [$name, $price, (int)$available, $photo, $id]
        );
    }

    /** @return bool false when the item appears in past orders (FK restriction) */
    public static function delete(int $id): bool
    {
        try {
            Database::query('DELETE FROM menu_items WHERE item_id = ?', [$id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    /** All available dishes for the public home page. */
    public static function allAvailableWithRestaurant(): array
    {
        return Database::query(
            'SELECT m.*, r.name AS restaurant_name FROM menu_items m JOIN restaurants r ON r.restaurant_id = m.restaurant_id WHERE m.available = 1 ORDER BY r.name, m.name'
        )->fetchAll();
    }

    /** Used by the live-search JSON API. */
    public static function search(string $q, int $limit = 20): array
    {
        $like = '%' . RestaurantModel::escapeLike($q) . '%';
        $stmt = Database::pdo()->prepare(
            'SELECT m.item_id, m.name, m.price, m.restaurant_id, r.name AS restaurant_name
               FROM menu_items m
               JOIN restaurants r ON r.restaurant_id = m.restaurant_id
              WHERE m.available = 1 AND m.name LIKE ?
              ORDER BY m.name
              LIMIT ?'
        );
        $stmt->bindValue(1, $like);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
