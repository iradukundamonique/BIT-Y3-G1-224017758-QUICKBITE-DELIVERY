-- Online Food Delivery System - seed data
-- First admin login: admin@quickbite.test / Admin@123 (you must change it immediately)
--               alice@quickbite.test (customer) / Customer@123
--               sam@quickbite.test   (rider)    / Rider@123
-- Import AFTER schema.sql

USE online_food_delivery;

INSERT INTO users (user_id, username, email, password_hash, role, must_change_password) VALUES
(1, 'admin',       'admin@quickbite.test', '$2y$10$f2q6iHZgesGeOZ7x6mMgfubj0lulQdCbGYLMzJJ4YOApAuNOgQuaC', 'admin', 1),
(2, 'alice',       'alice@quickbite.test', '$2y$10$iJaHnstVajPQ38VESSaOcOZW.oVZmGoDQ.5iewz9Q39ZvHSczRm9a', 'customer', 0),
(3, 'brian',       'brian@quickbite.test', '$2y$10$iJaHnstVajPQ38VESSaOcOZW.oVZmGoDQ.5iewz9Q39ZvHSczRm9a', 'customer', 0),
(4, 'carol',       'carol@quickbite.test', '$2y$10$iJaHnstVajPQ38VESSaOcOZW.oVZmGoDQ.5iewz9Q39ZvHSczRm9a', 'customer', 0),
(5, 'rider_sam',   'sam@quickbite.test',   '$2y$10$mylgXdUbQRXc16BejpsiSeSSQqwT6uBnLelxhUPg8EhDU5U.0m/ki', 'rider', 0),
(6, 'rider_lee',   'lee@quickbite.test',   '$2y$10$mylgXdUbQRXc16BejpsiSeSSQqwT6uBnLelxhUPg8EhDU5U.0m/ki', 'rider', 0);

INSERT INTO restaurants (restaurant_id, name, location, phone) VALUES
(1, 'Pizza Palace', '12 Market Street, Downtown', '+250788000001'),
(2, 'Spice Garden', '45 Riverside Avenue',        '+250788000002'),
(3, 'Burger Barn',  '7 Station Road',             '+250788000003'),
(4, 'Green Bowl',   '88 Park Lane, Uptown',       '+250788000004');

INSERT INTO menu_items (item_id, restaurant_id, name, price, available, photo) VALUES
(1, 1, 'Margherita Pizza', 1800.00, TRUE, NULL),
(2, 1, 'Pepperoni Pizza', 2000.00, TRUE, NULL),
(3, 1, 'Garlic Bread', 800.00, TRUE, NULL),
(4, 2, 'Chicken Tikka Masala', 2000.00, TRUE, NULL),
(5, 2, 'Veg Biryani', 1500.00, TRUE, NULL),
(6, 2, 'Garlic Naan', 500.00, TRUE, NULL),
(7, 3, 'Classic Burger', 1500.00, TRUE, NULL),
(8, 3, 'Cheese Fries', 800.00, TRUE, NULL),
(9, 3, 'Chocolate Shake', 1000.00, FALSE, NULL),
(10, 4, 'Quinoa Power Bowl', 1900.00, TRUE, NULL),
(11, 4, 'Avocado Wrap', 1400.00, TRUE, NULL),
(12, 4, 'Fresh Juice', 600.00, TRUE, NULL);

INSERT INTO orders (order_id, customer_id, restaurant_id, rider_id, status, total, delivery_address) VALUES
(1, 2, 1, 5,    'delivered',        4400.00, '5 Hilltop Close, Apt 3'),
(2, 3, 2, 6,    'out_for_delivery', 3000.00, '19 Lake View Road'),
(3, 2, 3, NULL, 'ready',            3800.00, '5 Hilltop Close, Apt 3'),
(4, 4, 4, NULL, 'pending',          3100.00, '102 Garden Estate, House 8');

INSERT INTO order_items (order_id, item_id, quantity, unit_price) VALUES
(1, 1, 2, 1800.00),
(1, 3, 1, 800.00),
(2, 4, 1, 2000.00),
(2, 6, 2, 500.00),
(3, 7, 2, 1500.00),
(3, 8, 1, 800.00),
(4, 10, 1, 1900.00),
(4, 12, 2, 600.00);
