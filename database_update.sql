CREATE DATABASE IF NOT EXISTS vehicle_db;
USE vehicle_db;

CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    vehicle_number VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    service_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL
);

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    card_last4 VARCHAR(4) DEFAULT NULL,
    transaction_ref VARCHAR(100) NOT NULL UNIQUE,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Default Admin: username = admin, password = admin123
-- This hash is the CORRECT bcrypt hash for "admin123"
INSERT IGNORE INTO admins (username, password_hash) VALUES (
    'admin',
    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdR5F0m3Hq6Vh8eUu'
);