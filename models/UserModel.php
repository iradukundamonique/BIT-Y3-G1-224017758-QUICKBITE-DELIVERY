<?php
declare(strict_types=1);

final class UserModel
{
    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM users WHERE user_id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $row = Database::query('SELECT * FROM users WHERE email = ?', [$email])->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::query('SELECT user_id, username, email, role, created_at FROM users ORDER BY user_id')->fetchAll();
    }

    public static function riders(): array
    {
        return Database::query("SELECT user_id, username FROM users WHERE role = 'rider' ORDER BY username")->fetchAll();
    }

    public static function emailTaken(string $email, int $exceptId = 0): bool
    {
        return (bool)Database::query(
            'SELECT 1 FROM users WHERE email = ? AND user_id <> ?',
            [$email, $exceptId]
        )->fetchColumn();
    }

    public static function create(string $username, string $email, string $password, string $role): int
    {
        Database::query(
            'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)',
            [$username, $email, password_hash($password, PASSWORD_DEFAULT), $role]
        );
        return (int)Database::pdo()->lastInsertId();
    }

    /** Pass $password = null to keep the current password. */
    public static function update(int $id, string $username, string $email, string $role, ?string $password): void
    {
        if ($password !== null && $password !== '') {
            Database::query(
                'UPDATE users SET username = ?, email = ?, role = ?, password_hash = ? WHERE user_id = ?',
                [$username, $email, $role, password_hash($password, PASSWORD_DEFAULT), $id]
            );
        } else {
            Database::query(
                'UPDATE users SET username = ?, email = ?, role = ? WHERE user_id = ?',
                [$username, $email, $role, $id]
            );
        }
    }

    public static function updatePasswordHash(int $id, string $password): void
    {
        Database::query('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE user_id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    /** @return bool false when the user still has orders (FK restriction) */
    public static function delete(int $id): bool
    {
        try {
            Database::query('DELETE FROM users WHERE user_id = ?', [$id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }
}
