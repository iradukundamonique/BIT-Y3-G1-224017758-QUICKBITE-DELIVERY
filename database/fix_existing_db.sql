-- Upgrade a database created from the ORIGINAL 4-table schema, WITHOUT losing data.
-- Run once in phpMyAdmin (SQL tab) with database `online_food_delivery` selected.
-- (If you prefer a clean start, just re-import schema.sql then seed.sql instead.)

USE online_food_delivery;

-- 1) Missing columns on orders
ALTER TABLE orders
    ADD COLUMN delivery_address VARCHAR(255) NOT NULL DEFAULT 'Not provided' AFTER total,
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'pending';

-- 2) Missing order_items table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT chk_oi_qty CHECK (quantity > 0),
    CONSTRAINT uq_order_item UNIQUE (order_id, item_id),
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_item FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
) ENGINE=InnoDB;

-- 3) Optional: rider deletion should not be blocked by old orders
ALTER TABLE orders DROP FOREIGN KEY fk_order_rider;
ALTER TABLE orders ADD CONSTRAINT fk_order_rider
    FOREIGN KEY (rider_id) REFERENCES users(user_id) ON DELETE SET NULL;

-- 4) Optional: menu items cascade when a restaurant is deleted
ALTER TABLE menu_items DROP FOREIGN KEY fk_menu_restaurant;
ALTER TABLE menu_items ADD CONSTRAINT fk_menu_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(restaurant_id) ON DELETE CASCADE;
