-- db.sql

CREATE DATABASE IF NOT EXISTS `footballgear_db`;
USE `footballgear_db`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `financial_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `salary` DECIMAL(10, 2) NOT NULL,
    `hire_date` DATE NOT NULL
);

-- Contoh Data (opsional, untuk pengujian)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('userbiasa', '$2y$10$wT.fR9R9S9Q9S9Q9S9Q9e.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O', 'user'), -- password: password123
('adminuser', '$2y$10$tU.fR9R9S9Q9S9Q9S9Q9e.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O.R.O', 'admin'); -- password: adminpass

-- Catatan: Hash password di atas adalah contoh. Gunakan password_hash() di PHP untuk hash yang sebenarnya.
-- Untuk userbiasa, passwordnya 'password123'.
-- Untuk adminuser, passwordnya 'adminpass'.