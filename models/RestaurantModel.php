<?php
declare(strict_types=1);

final class RestaurantModel
{
    /** All restaurants, optionally filtered by name/location. */
    public static function all(string $search = ''): array
    {
        if ($search === '') {
            return Database::query('SELECT * FROM restaurants ORDER BY name')->fetchAll();
        }
        $like = '%' . self::escapeLike($search) . '%';
        return Database::query(
            'SELECT * FROM restaurants WHERE name LIKE ? OR location LIKE ? ORDER BY name',
            [$like, $like]
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM restaurants WHERE restaurant_id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(string $name, string $location, string $phone): int
    {
        Database::query('INSERT INTO restaurants (name, location, phone) VALUES (?, ?, ?)', [$name, $location, $phone]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, string $name, string $location, string $phone): void
    {
        Database::query(
            'UPDATE restaurants SET name = ?, location = ?, phone = ? WHERE restaurant_id = ?',
            [$name, $location, $phone, $id]
        );
    }

    /** @return bool false when the restaurant has order history (FK restriction) */
    public static function delete(int $id): bool
    {
        try {
            Database::query('DELETE FROM restaurants WHERE restaurant_id = ?', [$id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    public static function escapeLike(string $s): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s);
    }
}
