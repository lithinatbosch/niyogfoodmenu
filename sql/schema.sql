-- Kids Menu Planner Database Schema
-- Database: kids_menu_planner

CREATE DATABASE IF NOT EXISTS kids_menu_planner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kids_menu_planner;

-- Food Categories Table
CREATE TABLE IF NOT EXISTS food_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Food Items Table
CREATE TABLE IF NOT EXISTS food_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES food_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Weekly Meal Plans Table
CREATE TABLE IF NOT EXISTS meal_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    meal_type ENUM('breakfast', 'morning_snack', 'lunch', 'evening_snack') NOT NULL,
    food_item_id INT NOT NULL,
    week_start_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meal (day_of_week, meal_type, week_start_date),
    INDEX idx_week_start (week_start_date),
    INDEX idx_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default food categories
INSERT INTO food_categories (name) VALUES 
    ('Breakfast'),
    ('Lunch'),
    ('Snack')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample food items
INSERT INTO food_items (name, category_id) VALUES
    -- Breakfast items
    ('Idli', 1),
    ('Dosa', 1),
    ('Upma', 1),
    ('Oats', 1),
    ('Poha', 1),
    ('Paratha', 1),
    
    -- Lunch items
    ('Rice', 2),
    ('Chapati', 2),
    ('Pasta', 2),
    ('Pulao', 2),
    ('Biryani', 2),
    ('Noodles', 2),
    
    -- Snack items
    ('Banana', 3),
    ('Apple', 3),
    ('Biscuits', 3),
    ('Sandwich', 3),
    ('Orange', 3),
    ('Grapes', 3),
    ('Yogurt', 3),
    ('Cookies', 3)
ON DUPLICATE KEY UPDATE name=name;
