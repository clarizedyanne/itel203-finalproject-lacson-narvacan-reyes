-- =============================================
-- USERS TABLE (Authentication)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- MENU ITEMS TABLE (Main Entity)
-- =============================================
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    price_variant VARCHAR(50),
    is_available TINYINT(1) DEFAULT 1,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- ORDERS TABLE (Transaction)
-- =============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','preparing','ready','completed','cancelled') DEFAULT 'pending',
    payment_method ENUM('gcash','cash') DEFAULT 'cash',
    payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- ORDER ITEMS TABLE (Related Table - One-to-Many)
-- =============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- =============================================
-- RESERVATIONS TABLE (Feature: Seat Reservation)
-- =============================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    res_date DATE NOT NULL,
    res_time TIME NOT NULL,
    pax INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT,
    payment_method ENUM('gcash','cash') DEFAULT 'gcash',
    payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- SEED: Default Admin & Customer Users
-- Passwords: password
-- =============================================
INSERT INTO users (name, email, password, role) VALUES
('Admin Oli', 'admin@oliscoffee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Juan Dela Cruz', 'customer@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- =============================================
-- SEED: Main Menu Items
-- =============================================

-- FOR SHARING (Flavored Boneless Chicken Bites)
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Yangnyeom (Spicy Korean)', 'Main', 'For Sharing', 'Flavored Boneless Chicken Bites', 279.00, NULL),
('Garlic Parmesan', 'Main', 'For Sharing', 'Flavored Boneless Chicken Bites', 279.00, NULL),
('Hickory Barbecue', 'Main', 'For Sharing', 'Flavored Boneless Chicken Bites', 279.00, NULL),
('Spicy Salted Egg', 'Main', 'For Sharing', 'Flavored Boneless Chicken Bites', 279.00, NULL);

-- RICE MEAL (Served with buttered vegetables)
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Chicken Fingers w/ Rice', 'Main', 'Rice Meal', 'Served with buttered vegetables', 189.00, NULL),
('Burger Steak w/ Egg', 'Main', 'Rice Meal', 'Served with buttered vegetables', 194.00, NULL),
('2pcs Grilled Porkchop w/ Mushroom Gravy', 'Main', 'Rice Meal', 'Served with buttered vegetables', 214.00, NULL),
('Chicken Fillet Ala King', 'Main', 'Rice Meal', 'Served with buttered vegetables', 199.00, NULL),
('Breaded Porkchop w/ Egg', 'Main', 'Rice Meal', 'Served with buttered vegetables', 194.00, NULL),
('Fish Fillet w/ Rice in Tartar Sauce', 'Main', 'Rice Meal', 'Served with buttered vegetables', 194.00, NULL),
('Flavored Chicken Bites w/ Rice', 'Main', 'Rice Meal', 'Yangnyeom, Garlic Parmesan, Hickory BBQ, Spicy Salted Egg', 199.00, NULL),
('4pcs Chicken Wings w/ Rice', 'Main', 'Rice Meal', 'Yangnyeom, Garlic Parmesan, Hickory BBQ, Spicy Salted Egg', 199.00, NULL);

-- CHICKEN WINGS (6pcs)
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Yangnyeom Wings (Spicy Korean) 6pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 239.00, '6pcs'),
('Garlic Parmesan Wings 6pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 239.00, '6pcs'),
('Hickory Barbecue Wings 6pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 239.00, '6pcs'),
('Spicy Salted Egg Wings 6pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 239.00, '6pcs');

-- CHICKEN WINGS (12pcs)
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Yangnyeom Wings (Spicy Korean) 12pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 459.00, '12pcs'),
('Garlic Parmesan Wings 12pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 459.00, '12pcs'),
('Hickory Barbecue Wings 12pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 459.00, '12pcs'),
('Spicy Salted Egg Wings 12pcs', 'Main', 'Chicken Wings', 'Flavored Chicken Wings', 459.00, '12pcs');

-- =============================================
-- SEED: Snacks
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Nachos', 'Snacks', 'Snacks', '', 198.00, NULL),
('Chicken Fingers', 'Snacks', 'Snacks', '', 189.00, NULL),
('Cheesy Bacon Fries', 'Snacks', 'Snacks', '', 198.00, NULL),
('Fish & Fries', 'Snacks', 'Snacks', '', 198.00, NULL),
('Flavored Fries', 'Snacks', 'Snacks', 'Barbecue · Cheese · Sour Cream', 159.00, NULL),
('Flavored Mojos', 'Snacks', 'Snacks', 'Barbecue · Cheese · Sour Cream', 189.00, NULL);

-- =============================================
-- SEED: Pasta
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Gourmet Tuyo Pasta', 'Pasta', 'Pasta', 'Served with Garlic Bread', 189.00, NULL),
('Alfredo (White Sauce)', 'Pasta', 'Pasta', 'Served with Garlic Bread', 194.00, NULL),
('Meat Sauce Spaghetti', 'Pasta', 'Pasta', 'Served with Garlic Bread', 189.00, NULL),
('Lasagna', 'Pasta', 'Pasta', 'Served with Garlic Bread', 194.00, NULL),
('Aligue Pasta', 'Pasta', 'Pasta', 'Served with Garlic Bread', 189.00, NULL),
('Shrimp Aglio Olio', 'Pasta', 'Pasta', 'Served with Garlic Bread', 194.00, NULL),
('Chicken Oriental Pasta', 'Pasta', 'Pasta', 'Served with Garlic Bread', 189.00, NULL);

-- =============================================
-- SEED: Burgers / Sandwiches
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Pulled Pork BBQ', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 189.00, NULL),
('Dori Fish Burger', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 189.00, NULL),
('Cheeseburger', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 194.00, NULL),
('Bacon Cheeseburger', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 209.00, NULL),
('Crispy Chicken Burger', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 194.00, NULL),
('Clubhouse Sandwich', 'Burgers', 'Burgers/Sandwiches', 'Served with Fries', 194.00, NULL);

-- =============================================
-- SEED: Salads
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('Macaroni Salad', 'Salads', 'Salads', '', 169.00, NULL),
('Kani Salad', 'Salads', 'Salads', 'Lettuce, Cucumber, Carrots, Mango, Crab Sticks, Roasted Sesame dressing', 189.00, NULL),
('Chicken Caesar Salad', 'Salads', 'Salads', 'Romaine Lettuce, Chicken breast, Croutons, Parmesan, Caesar dressing, bacon bits', 209.00, NULL);

-- =============================================
-- SEED: Pizza
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
('All Cheese', 'Pizza', 'Classic', 'New York Style', 329.00, '12"'),
('All Cheese', 'Pizza', 'Classic', 'New York Style', 449.00, '16"'),
('American Ham and Cheese', 'Pizza', 'Classic', 'New York Style', 349.00, '12"'),
('American Ham and Cheese', 'Pizza', 'Classic', 'New York Style', 469.00, '16"'),
('Hawaiian', 'Pizza', 'Classic', 'New York Style', 359.00, '12"'),
('Hawaiian', 'Pizza', 'Classic', 'New York Style', 479.00, '16"'),
("New York's Pepperoni", 'Pizza', 'Premium', 'New York Style', 389.00, '12"'),
("New York's Pepperoni", 'Pizza', 'Premium', 'New York Style', 499.00, '16"'),
('Hawaiian Supreme', 'Pizza', 'Premium', 'New York Style', 399.00, '12"'),
('Hawaiian Supreme', 'Pizza', 'Premium', 'New York Style', 509.00, '16"'),
('All Meat', 'Pizza', 'Premium', 'New York Style', 399.00, '12"'),
('All Meat', 'Pizza', 'Premium', 'New York Style', 509.00, '16"'),
("New York's Special", 'Pizza', 'Premium', 'Everything on it', 399.00, '12"'),
("New York's Special", 'Pizza', 'Premium', 'Everything on it', 509.00, '16"'),
('Carbonara Pizza', 'Pizza', 'Premium', 'White Sauce', 399.00, '12"'),
('Carbonara Pizza', 'Pizza', 'Premium', 'White Sauce', 509.00, '16"'),
('Pulled Pork BBQ Pizza', 'Pizza', 'Premium', 'New York Style', 399.00, '12"'),
('Pulled Pork BBQ Pizza', 'Pizza', 'Premium', 'New York Style', 509.00, '16"'),
('4 Cheese Pizza', 'Pizza', 'Latest Special', 'New York Style', 409.00, '12"'),
('4 Cheese Pizza', 'Pizza', 'Latest Special', 'New York Style', 529.00, '16"'),
('Garlic Shrimp Pizza', 'Pizza', 'Latest Special', 'New York Style', 409.00, '12"'),
('Garlic Shrimp Pizza', 'Pizza', 'Latest Special', 'New York Style', 529.00, '16"');

-- =============================================
-- SEED: Drinks
-- =============================================
INSERT INTO menu_items (name, category, subcategory, description, price, price_variant) VALUES
-- Artisan Tea
('Pearl Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 95.00, '16oz'),
('Pearl Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '22oz'),
('Earl Grey Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '16oz'),
('Earl Grey Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 115.00, '22oz'),
('Ceylon Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '16oz'),
('Ceylon Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 115.00, '22oz'),
('Sun Moon Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '16oz'),
('Sun Moon Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 115.00, '22oz'),
('Jasmine Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '16oz'),
('Jasmine Milk Tea', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 115.00, '22oz'),
('Cookies and Cream', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 105.00, '16oz'),
('Cookies and Cream', 'Drinks', 'Artisan Tea', 'Free Pearl Sinker', 115.00, '22oz'),
-- Milk Tea
('Wintermelon', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 85.00, '16oz'),
('Wintermelon', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '22oz'),
('Okinawa', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 85.00, '16oz'),
('Okinawa', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '22oz'),
('Taro', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 85.00, '16oz'),
('Taro', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '22oz'),
('Dark Chocolate', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '16oz'),
('Dark Chocolate', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 105.00, '22oz'),
('Red Velvet Milk Tea', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '16oz'),
('Red Velvet Milk Tea', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 105.00, '22oz'),
('Matcha Milk Tea', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '16oz'),
('Matcha Milk Tea', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 105.00, '22oz'),
('Brown Sugar Milk', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 95.00, '16oz'),
('Brown Sugar Milk', 'Drinks', 'Milk Tea', 'Free Pearl Sinker', 105.00, '22oz'),
-- Hot Tea
('Earl Grey Hot Tea', 'Drinks', 'Hot Tea', '', 95.00, '12oz'),
('Earl Grey Hot Tea', 'Drinks', 'Hot Tea', '', 105.00, '16oz'),
('Ceylon Hot Tea', 'Drinks', 'Hot Tea', '', 95.00, '12oz'),
('Ceylon Hot Tea', 'Drinks', 'Hot Tea', '', 105.00, '16oz'),
('Sun Moon Hot Tea', 'Drinks', 'Hot Tea', '', 95.00, '12oz'),
('Sun Moon Hot Tea', 'Drinks', 'Hot Tea', '', 105.00, '16oz'),
('Jasmine Hot Tea', 'Drinks', 'Hot Tea', '', 95.00, '12oz'),
('Jasmine Hot Tea', 'Drinks', 'Hot Tea', '', 105.00, '16oz'),
-- Cheesecake Series
('Classic Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Classic Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Earl Grey Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Earl Grey Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Sun Moon Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Sun Moon Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Red Velvet Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Red Velvet Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Dark Choco Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Dark Choco Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Oreo Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Oreo Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Okinawa Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Okinawa Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Taro Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Taro Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
('Matcha Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 125.00, '16oz'),
('Matcha Cheesecake', 'Drinks', 'Cheesecake', 'Free Pearl Sinker', 140.00, '22oz'),
-- Rock Salt & Cheese
('Classic RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 125.00, '16oz'),
('Classic RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 140.00, '22oz'),
('Earl Grey RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 125.00, '16oz'),
('Earl Grey RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 140.00, '22oz'),
('SunMoon RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 125.00, '16oz'),
('SunMoon RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 140.00, '22oz'),
('Okinawa RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 125.00, '16oz'),
('Okinawa RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 140.00, '22oz'),
('Dark Choco RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 125.00, '16oz'),
('Dark Choco RSC', 'Drinks', 'Rock Salt & Cheese', 'Free Pearl Sinker', 140.00, '22oz'),
-- Hot Drinks
('Americano', 'Drinks', 'Hot Drinks', '', 105.00, '12oz'),
('Americano', 'Drinks', 'Hot Drinks', '', 120.00, '16oz'),
('Latte', 'Drinks', 'Hot Drinks', '', 120.00, '12oz'),
('Latte', 'Drinks', 'Hot Drinks', '', 135.00, '16oz'),
('Cappuccino', 'Drinks', 'Hot Drinks', '', 120.00, '12oz'),
('Cappuccino', 'Drinks', 'Hot Drinks', '', 135.00, '16oz'),
('Hot Choco', 'Drinks', 'Hot Drinks', '', 125.00, '12oz'),
('Hot Choco', 'Drinks', 'Hot Drinks', '', 140.00, '16oz'),
('Green Tea Latte', 'Drinks', 'Hot Drinks', '', 125.00, '12oz'),
('Green Tea Latte', 'Drinks', 'Hot Drinks', '', 140.00, '16oz'),
('Mocha', 'Drinks', 'Hot Drinks', '', 130.00, '12oz'),
('Mocha', 'Drinks', 'Hot Drinks', '', 145.00, '16oz'),
('Caramel Macchiato', 'Drinks', 'Hot Drinks', '', 130.00, '12oz'),
('Caramel Macchiato', 'Drinks', 'Hot Drinks', '', 145.00, '16oz'),
('Hazelnut Latte', 'Drinks', 'Hot Drinks', '', 130.00, '12oz'),
('Hazelnut Latte', 'Drinks', 'Hot Drinks', '', 145.00, '16oz'),
('Vanilla Latte', 'Drinks', 'Hot Drinks', '', 130.00, '12oz'),
('Vanilla Latte', 'Drinks', 'Hot Drinks', '', 145.00, '16oz'),
-- Iced Drinks
('Iced Americano', 'Drinks', 'Iced Drinks', '', 105.00, '16oz'),
('Iced Americano', 'Drinks', 'Iced Drinks', '', 120.00, '22oz'),
('Iced Latte', 'Drinks', 'Iced Drinks', '', 125.00, '16oz'),
('Iced Latte', 'Drinks', 'Iced Drinks', '', 140.00, '22oz'),
('Iced Caramel Macchiato', 'Drinks', 'Iced Drinks', '', 130.00, '16oz'),
('Iced Caramel Macchiato', 'Drinks', 'Iced Drinks', '', 145.00, '22oz'),
('Iced Mocha', 'Drinks', 'Iced Drinks', '', 130.00, '16oz'),
('Iced Mocha', 'Drinks', 'Iced Drinks', '', 145.00, '22oz'),
('Iced Hazelnut', 'Drinks', 'Iced Drinks', '', 130.00, '16oz'),
('Iced Hazelnut', 'Drinks', 'Iced Drinks', '', 145.00, '22oz'),
('Iced Matcha Latte', 'Drinks', 'Iced Drinks', '', 130.00, '16oz'),
('Iced Matcha Latte', 'Drinks', 'Iced Drinks', '', 145.00, '22oz'),
-- Ice Blended
('Chocolate Chip Mocha', 'Drinks', 'Ice Blended', '', 130.00, '16oz'),
('Chocolate Chip Mocha', 'Drinks', 'Ice Blended', '', 145.00, '22oz'),
('Mocha Frappe', 'Drinks', 'Ice Blended', '', 130.00, '16oz'),
('Mocha Frappe', 'Drinks', 'Ice Blended', '', 145.00, '22oz'),
('Espresso Hazelnut Frappe', 'Drinks', 'Ice Blended', '', 130.00, '16oz'),
('Espresso Hazelnut Frappe', 'Drinks', 'Ice Blended', '', 145.00, '22oz'),
('Caramel Frappe', 'Drinks', 'Ice Blended', '', 130.00, '16oz'),
('Caramel Frappe', 'Drinks', 'Ice Blended', '', 145.00, '22oz'),
('Java Chip', 'Drinks', 'Ice Blended', '', 130.00, '16oz'),
('Java Chip', 'Drinks', 'Ice Blended', '', 145.00, '22oz'),
('Coffee Jelly Frappe', 'Drinks', 'Ice Blended', '', 135.00, '16oz'),
('Coffee Jelly Frappe', 'Drinks', 'Ice Blended', '', 150.00, '22oz'),
('Dark Choco Espresso', 'Drinks', 'Ice Blended', '', 135.00, '16oz'),
('Dark Choco Espresso', 'Drinks', 'Ice Blended', '', 150.00, '22oz'),
-- Cream Based
('Chocolate Milkshake', 'Drinks', 'Cream Based', '', 115.00, '16oz'),
('Chocolate Milkshake', 'Drinks', 'Cream Based', '', 130.00, '22oz'),
('Vanilla Milkshake', 'Drinks', 'Cream Based', '', 115.00, '16oz'),
('Vanilla Milkshake', 'Drinks', 'Cream Based', '', 130.00, '22oz'),
('Oreo Cream Frappe', 'Drinks', 'Cream Based', '', 125.00, '16oz'),
('Oreo Cream Frappe', 'Drinks', 'Cream Based', '', 140.00, '22oz'),
('Strawberry Milkshake', 'Drinks', 'Cream Based', '', 125.00, '16oz'),
('Strawberry Milkshake', 'Drinks', 'Cream Based', '', 140.00, '22oz'),
('Mango Milkshake', 'Drinks', 'Cream Based', '', 125.00, '16oz'),
('Mango Milkshake', 'Drinks', 'Cream Based', '', 140.00, '22oz');

-- =============================================
-- AUTO BACKUP EVENT (runs daily at midnight)
-- =============================================
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS daily_backup_log
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
  INSERT INTO users (name, email, password, role)
  SELECT name, email, password, role FROM users WHERE 1=0;
-- Note: Real backup should use mysqldump via cron on server
