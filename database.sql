CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `poster_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_name` VARCHAR(255) DEFAULT 'Untitled Template',
    `template_path` VARCHAR(255) NOT NULL,
    `canvas_width` INT DEFAULT 1080,
    `canvas_height` INT DEFAULT 1080,
    `image_x` INT DEFAULT 0,
    `image_y` INT DEFAULT 0,
    `image_width` INT DEFAULT 300,
    `image_height` INT DEFAULT 300,
    `image_size` INT DEFAULT 300,
    `image_shape` ENUM('circle', 'square', 'rounded') DEFAULT 'circle',
    `name_x` INT DEFAULT 540,
    `name_y` INT DEFAULT 900,
    `name_font_size` INT DEFAULT 60,
    `name_color` VARCHAR(20) DEFAULT '#000000',
    `designation_x` INT DEFAULT 540,
    `designation_y` INT DEFAULT 980,
    `designation_font_size` INT DEFAULT 40,
    `designation_color` VARCHAR(20) DEFAULT '#333333',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin (User: admin, Pass: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO `admins` (`username`, `password`) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS `generated_posters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL UNIQUE,
    `user_name` VARCHAR(255) DEFAULT '',
    `template_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
