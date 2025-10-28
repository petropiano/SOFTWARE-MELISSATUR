CREATE DATABASE IF NOT EXISTS pixelteca_db;
USE pixelteca_db;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE, 
    email VARCHAR(100) NOT NULL UNIQUE,    
    password_hash VARCHAR(255) NOT NULL,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;


CREATE TABLE game_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                  
    game_name VARCHAR(255) NOT NULL,       
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),  
    review TEXT NOT NULL,                  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_game_name (game_name)
) ENGINE=InnoDB;


INSERT INTO users (username, email, password_hash) VALUES 
('testuser', 'test@example.com', '$2y$10$examplehashedpassword'); 

-- Sample review
INSERT INTO game_reviews (user_id, game_name, rating, review) VALUES 
(1, 'The Legend of Zelda: Breath of the Wild', 5, 'Incredible game with amazing open world!');
