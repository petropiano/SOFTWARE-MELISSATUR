-- Pixelteca Database Setup
-- Based on original HTML/CSS for login/register and game rating features
-- Run this in phpMyAdmin or via MySQL command line: mysql -u root -p < pixelteca_db.sql

-- Create the database (if it doesn't exist)
CREATE DATABASE IF NOT EXISTS pixelteca_db;
USE pixelteca_db;

-- Users table (from original login/register HTML: username, email, password)
-- Stores user accounts with hashed passwords for security
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,  -- From register-username input
    email VARCHAR(100) NOT NULL UNIQUE,    -- From register-email input
    password_hash VARCHAR(255) NOT NULL,  -- Hashed version of register-password (use password_hash() in PHP)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Game reviews table (for rating games with stars and analysis)
-- Ties reviews to users (assuming logged-in users rate games)
CREATE TABLE game_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                  -- Links to users.id (for logged-in ratings)
    game_name VARCHAR(255) NOT NULL,       -- Name of the game from IGDB
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),  -- 5-star rating
    review TEXT NOT NULL,                  -- Written analysis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_game_name (game_name)
) ENGINE=InnoDB;

-- Optional: Insert sample data for testing (remove in production)
-- Sample user (password is 'password123' hashed with password_hash())
INSERT INTO users (username, email, password_hash) VALUES 
('testuser', 'test@example.com', '$2y$10$examplehashedpassword');  -- Replace with real hash

-- Sample review
INSERT INTO game_reviews (user_id, game_name, rating, review) VALUES 
(1, 'The Legend of Zelda: Breath of the Wild', 5, 'Incredible game with amazing open world!');
