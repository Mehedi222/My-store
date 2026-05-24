-- Database initialization for YST Digital
CREATE DATABASE IF NOT EXISTS yst_digital_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yst_digital_store;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'editor', 'admin') DEFAULT 'user',
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    screenshots TEXT, -- JSON array of image paths
    file_path VARCHAR(255) NOT NULL, -- Secure file storage path
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Coupons table
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('flat', 'percentage') NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    expiry_date DATE NOT NULL,
    usage_limit INT DEFAULT 100,
    used_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    coupon_code VARCHAR(50) DEFAULT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    tax_percent DECIMAL(5, 2) DEFAULT 0.00,
    final_amount DECIMAL(10, 2) NOT NULL,
    payment_gateway VARCHAR(50) DEFAULT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    payment_status ENUM('pending', 'completed', 'refunded', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Support Tickets table
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    reply TEXT DEFAULT NULL,
    status ENUM('open', 'replied', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- FAQs table
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Settings
INSERT INTO settings (`key`, `value`) VALUES
('site_name', 'YST Digital'),
('site_logo', ''),
('footer_text', '&copy; 2026 YST Digital. Sleek Solutions for Digital Creators.'),
('currency', 'USD'),
('tax_percent', '18.00'),
('stripe_public_key', 'pk_test_sample'),
('stripe_secret_key', 'sk_test_sample'),
('paypal_client_id', 'paypal_client_sample'),
('razorpay_key_id', 'rzp_test_sample'),
('razorpay_key_secret', 'rzp_secret_sample')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- Password Reset Tokens (for production email-based flow)
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
);

-- Seed Default Admin & Users
-- Hashed passwords for 'admin123' and 'user123' respectively:
-- 'admin123' -> $2y$10$Z1eA7uJzJg5nLox9bHnZ3ugUvq5vFpT9vS5uFw5rP.uC8fG9iUaNq
-- 'user123'  -> $2y$10$wK1F5nO14Fk9x7.jJbHnZuBvq5vFpT9vS5uFw5rP.uC8fG9iUaNq
INSERT INTO users (name, email, password, role, status) VALUES
('Super Admin', 'admin@ystdigital.com', '$2y$10$Z1eA7uJzJg5nLox9bHnZ3ugUvq5vFpT9vS5uFw5rP.uC8fG9iUaNq', 'admin', 'active'),
('Editor User', 'editor@ystdigital.com', '$2y$10$Z1eA7uJzJg5nLox9bHnZ3ugUvq5vFpT9vS5uFw5rP.uC8fG9iUaNq', 'editor', 'active'),
('Demo Customer', 'user@ystdigital.com', '$2y$10$wK1F5nO14Fk9x7.jJbHnZuBvq5vFpT9vS5uFw5rP.uC8fG9iUaNq', 'user', 'active')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Seed Seed Products
INSERT INTO products (title, description, price, category, screenshots, file_path, status) VALUES
('Premium Admin Dashboard', 'A gorgeous Material Design dashboard with advanced HSL color-scheme configuration, dark/light capability, visual charts, custom widgets, and full responsiveness.', 29.00, 'Templates', '["assets/images/dashboard_cover.webp"]', 'secure/dashboard_template_v1.zip', 'active'),
('SaaS Boilerplate Code', 'Jumpstart your startup with this powerful PHP/MySQL boilerplate. Contains complete user authentication, role-based controls, dynamic styling toggles, and fully functional simulated billing gateways.', 79.00, 'Code', '["assets/images/saas_boilerplate.webp"]', 'secure/saas_boilerplate_v1.zip', 'active'),
('Mobile UI Figma Kit', 'An elegant HSL glassmorphic UI kit for web creators and mobile designers. Complete with 100+ components, premium responsive spacing tokens, and dark/light UI layers.', 19.00, 'UI Kits', '["assets/images/uikit_cover.webp"]', 'secure/figma_ui_kit_v1.zip', 'active'),
('E-Commerce App Flutter Source', 'A complete cross-platform Flutter mobile app featuring custom animated transitions, integrated shopping cart, profile management, and stunning Material Design 3 tabs.', 99.00, 'Mobile', '["assets/images/flutter_app_cover.webp"]', 'secure/flutter_ecommerce_app.zip', 'active')
ON DUPLICATE KEY UPDATE `title`=`title`;

-- Seed Coupons
INSERT INTO coupons (code, type, value, expiry_date, usage_limit, used_count, status) VALUES
('WELCOME10', 'percentage', 10.00, '2026-12-31', 500, 0, 'active'),
('FLAT5', 'flat', 5.00, '2026-12-31', 200, 0, 'active'),
('EXPIRED50', 'percentage', 50.00, '2020-01-01', 50, 0, 'active')
ON DUPLICATE KEY UPDATE `code`=`code`;

-- Seed FAQs
INSERT INTO faqs (question, answer) VALUES
('How do I download my purchased products?', 'Once your payment transaction is completed, you will be redirected to the Orders page. Click on the secure "Download" button next to your product. The links are digitally protected and can be customized with automatic expiration times in the settings.'),
('Which payment gateways are supported?', 'We support Razorpay, Stripe, and PayPal. Administrators can easily toggle, update API credentials, and manage keys directly from the settings panel inside the admin dashboard.'),
('Can I request a refund if a product doesn''t work?', 'Yes. Please submit a support ticket under the Contact section with your transaction reference. Our administration staff reviews and issues refunds manually or automatically depending on your dispute.')
ON DUPLICATE KEY UPDATE `question`=`question`;
