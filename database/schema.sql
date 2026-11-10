-- BPM (Beats Per Minute) Database Schema
-- Charset: utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users table (Telegram Auth supported)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `telegram_id` BIGINT UNIQUE NULL,
  `first_name` VARCHAR(120) NOT NULL,
  `last_name` VARCHAR(120) NULL,
  `username` VARCHAR(120) NULL,
  `photo_url` TEXT NULL,
  `phone` VARCHAR(30) NULL,
  `city` VARCHAR(100) NULL,
  `address` TEXT NULL,
  `auth_date` INT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `category_slug` ENUM('clothes', 'prints') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `old_price` DECIMAL(10, 2) NULL,
  `short_desc` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `image` TEXT NOT NULL,
  `images_gallery` TEXT NULL, -- JSON array of image URLs
  `sizes` VARCHAR(150) DEFAULT 'S, M, L, XL, OVERSIZE',
  `material` VARCHAR(150) DEFAULT '500 GSM Премиальный хлопок',
  `badge` VARCHAR(50) NULL, -- 'HOT DROP', 'VELCRO READY', 'LIMITED 50'
  `velcro_zone` VARCHAR(150) DEFAULT 'Грудь (20x15 см), Спина (30x20 см)',
  `print_preview_url` TEXT NULL, -- SVG or PNG overlay for interactive print customizer
  `is_featured` TINYINT(1) DEFAULT 0,
  `stock` INT DEFAULT 50,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Promo codes table with conditional rules
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_type` ENUM('percent', 'fixed', 'second_print_percent', 'bundle_hoodie_print') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10, 2) NOT NULL,
  `min_order_amount` DECIMAL(10, 2) DEFAULT 0,
  `description` VARCHAR(255) NOT NULL,
  `max_uses` INT DEFAULT 1000,
  `used_count` INT DEFAULT 0,
  `expires_at` DATE NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_telegram` VARCHAR(120) NULL,
  `customer_city` VARCHAR(100) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `delivery_type` VARCHAR(50) DEFAULT 'СДЭК',
  `payment_method` VARCHAR(50) DEFAULT 'Оплата при получении / СБП',
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `discount_amount` DECIMAL(10, 2) DEFAULT 0,
  `delivery_cost` DECIMAL(10, 2) DEFAULT 0,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `promo_code` VARCHAR(50) NULL,
  `status` ENUM('new', 'processing', 'assembled', 'shipped', 'delivered', 'cancelled') DEFAULT 'new',
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NULL,
  `product_title` VARCHAR(255) NOT NULL,
  `product_category` VARCHAR(50) NOT NULL,
  `product_image` TEXT NULL,
  `selected_size` VARCHAR(50) NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `quantity` INT DEFAULT 1,
  `total` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Likes / Wishlist table
CREATE TABLE IF NOT EXISTS `likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Recently viewed table
CREATE TABLE IF NOT EXISTS `recently_viewed` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `viewed_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_recent_unique` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Shop settings table
CREATE TABLE IF NOT EXISTS `shop_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
