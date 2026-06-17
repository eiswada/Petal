-- ============================================
-- PETAL - Database SQL 
-- ============================================

CREATE DATABASE IF NOT EXISTS petal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petal_db;

-- 1. Colors Table
CREATE TABLE IF NOT EXISTS colors (
    name VARCHAR(20) PRIMARY KEY,
    bg_hex VARCHAR(10) NOT NULL,
    dark_hex VARCHAR(10) NOT NULL
);

-- 2. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Private Messages Table
CREATE TABLE IF NOT EXISTS private_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    email_tujuan VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    tanggal_kirim DATE NOT NULL,
    color VARCHAR(20) DEFAULT 'pink',
    font VARCHAR(20) DEFAULT 'sans',
    status ENUM('pending', 'sent', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (color) REFERENCES colors(name) ON DELETE SET NULL
);

-- 4. Public Messages Table
CREATE TABLE IF NOT EXISTS public_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    untuk_siapa VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    color VARCHAR(20) DEFAULT 'pink',
    font VARCHAR(20) DEFAULT 'sans',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (color) REFERENCES colors(name) ON DELETE SET NULL
);

-- 5. Admin Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    target_table VARCHAR(50),
    target_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
-- SEED DATA
-- ============================================

-- Seed Colors
INSERT INTO colors (name, bg_hex, dark_hex) VALUES
('pink', '#f9a8c9', '#e879a8'),
('purple', '#c4b5fd', '#7c5cbf'),
('white', '#ffffff', '#6b7280'),
('blue', '#93c5fd', '#2563eb'),
('yellow', '#fde68a', '#ca8a04')
ON DUPLICATE KEY UPDATE bg_hex=VALUES(bg_hex), dark_hex=VALUES(dark_hex);

-- Seed Admin User (password: admin123)
INSERT INTO users (id, username, email, password, avatar) VALUES 
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL)
ON DUPLICATE KEY UPDATE username=VALUES(username), email=VALUES(email);

-- Seed Private Messages
INSERT INTO private_messages (sender_name, email_tujuan, pesan, tanggal_kirim, color, font) VALUES
('Rina', 'rina@example.com', 'Halo diriku di masa depan! Semoga kamu sudah lebih berani dari sekarang.', '2026-12-31', 'pink', 'sans'),
('Budi', 'budi@example.com', 'Dear future me, jangan lupa mimpi kita waktu kuliah ya.', '2027-06-01', 'purple', 'serif'),
('Sari', 'sari@example.com', 'Kalau kamu baca ini, artinya kamu sudah melewati semua itu. Proud of you.', '2026-09-15', 'pink', 'sans'),
('Dito', 'dito@example.com', 'Semoga bisnis kecil kita sudah berkembang ya.', '2027-01-01', 'white', 'mono'),
('Maya', 'maya@example.com', 'Tetap jadi dirimu sendiri, no matter what.', '2026-11-20', 'blue', 'sans');

-- Seed Public Messages
INSERT INTO public_messages (untuk_siapa, pesan, color, font) VALUES
('Dunia', 'Semoga kalian baik-baik saja di luar sana. Kita semua sedang berjuang.', 'pink', 'sans'),
('Siapapun yang lagi sedih', 'Ini pun akan berlalu. Percayalah.', 'purple', 'serif'),
('Generasi berikutnya', 'Kami mencintai bumi ini, tolong jaga ia baik-baik.', 'white', 'mono'),
('Diriku sendiri', 'Kamu lebih kuat dari yang kamu kira.', 'yellow', 'sans'),
('Semua orang', 'Jangan lupa untuk beristirahat. Kamu tidak harus selalu produktif.', 'blue', 'sans');

-- ============================================
-- VIEWS 
-- ============================================

-- View 1: Public Messages Summary with Color info
CREATE OR REPLACE VIEW v_public_messages_summary AS
SELECT 
    pm.id, 
    pm.untuk_siapa, 
    pm.pesan, 
    pm.created_at, 
    pm.color,
    pm.font,
    c.bg_hex, 
    c.dark_hex
FROM public_messages pm
LEFT JOIN colors c ON pm.color = c.name;

-- View 2: Admin Activity logs with username
CREATE OR REPLACE VIEW v_admin_activity_log AS
SELECT 
    al.id, 
    u.username,
    al.action, 
    al.target_table, 
    al.target_id, 
    al.created_at
FROM admin_logs al
LEFT JOIN users u ON al.user_id = u.id;

-- ============================================
-- FUNCTIONS
-- ============================================

-- Function 1: Get Total messages in database (public + private)
DROP FUNCTION IF EXISTS get_total_messages;
DELIMITER //
CREATE FUNCTION get_total_messages()
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE total INT;
    SELECT 
        (SELECT COUNT(*) FROM private_messages) + 
        (SELECT COUNT(*) FROM public_messages) 
    INTO total;
    RETURN total;
END //
DELIMITER ;

-- Function 2: Check if message is due for delivery
DROP FUNCTION IF EXISTS is_delivery_due;
DELIMITER //
CREATE FUNCTION is_delivery_due(tanggal_kirim DATE, status_pesan VARCHAR(20))
RETURNS INT
DETERMINISTIC
NO SQL
BEGIN
    IF tanggal_kirim <= CURDATE() AND status_pesan = 'pending' THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;
END //
DELIMITER ;

-- ============================================
-- TRIGGERS 
-- ============================================

-- Trigger 1: Log public message deletion
DROP TRIGGER IF EXISTS before_public_message_delete;
DELIMITER //
CREATE TRIGGER before_public_message_delete
BEFORE DELETE ON public_messages
FOR EACH ROW
BEGIN
    INSERT INTO admin_logs (user_id, action, target_table, target_id)
    VALUES (1, 'DELETE_PUBLIC_MESSAGE', 'public_messages', OLD.id);
END //
DELIMITER ;

-- Trigger 2: Log private message deletion
DROP TRIGGER IF EXISTS before_private_message_delete;
DELIMITER //
CREATE TRIGGER before_private_message_delete
BEFORE DELETE ON private_messages
FOR EACH ROW
BEGIN
    INSERT INTO admin_logs (user_id, action, target_table, target_id)
    VALUES (1, 'DELETE_PRIVATE_MESSAGE', 'private_messages', OLD.id);
END //
DELIMITER ;