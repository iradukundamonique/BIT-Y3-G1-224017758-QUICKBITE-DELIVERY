-- Run once with online_food_delivery selected in phpMyAdmin.
USE online_food_delivery;
ALTER TABLE menu_items ADD COLUMN photo VARCHAR(255) NULL AFTER available;
ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
-- Require the seeded administrator to change the initial password at first login.
UPDATE users SET must_change_password = 1 WHERE email = 'admin@quickbite.test' AND role = 'admin';
-- Ensure the initial administrator login exists if this database did not have it.
INSERT INTO users (username, email, password_hash, role, must_change_password)
SELECT 'admin', 'admin@quickbite.test', '$2y$10$f2q6iHZgesGeOZ7x6mMgfubj0lulQdCbGYLMzJJ4YOApAuNOgQuaC', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@quickbite.test');
