-- GoTrip Travel Website Database Structure
-- This file contains all the necessary tables for the GoTrip travel booking website

-- Create the database
CREATE DATABASE IF NOT EXISTS travel_tourism;
USE travel_tourism;

-- Users table for storing user account information
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table for storing all travel bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(10) NOT NULL,
    user_id INT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    destination VARCHAR(255) NOT NULL,
    travel_date DATE NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    travelers INT NOT NULL DEFAULT 1,
    room_type VARCHAR(50),
    airport_pickup TINYINT(1) NOT NULL DEFAULT 0,
    tour_guide TINYINT(1) NOT NULL DEFAULT 0,
    insurance TINYINT(1) NOT NULL DEFAULT 0,
    special_requests TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
    INDEX (user_id),
    INDEX (booking_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Destinations table for storing travel destinations information
CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    country VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    rating FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Travel alerts table for storing warnings and alerts for destinations
CREATE TABLE IF NOT EXISTS travel_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT,
    alert_type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE,
    end_date DATE,
    severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact messages table for storing customer inquiries
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessions table for managing user sessions
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45) NOT NULL,
    timestamp INT(10) UNSIGNED NOT NULL DEFAULT 0,
    data TEXT NOT NULL,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews table for storing user reviews of destinations
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(100),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    INDEX (destination_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rewards table for user loyalty points
CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT NOT NULL DEFAULT 0,
    level ENUM('Bronze', 'Silver', 'Gold', 'Platinum') DEFAULT 'Bronze',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rewards transactions table to track point history
CREATE TABLE IF NOT EXISTS reward_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT,
    points INT NOT NULL,
    transaction_type ENUM('earn', 'redeem') NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX (user_id),
    INDEX (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist table for users to save destinations they're interested in
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, destination_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User travel history table for storing past travels
CREATE TABLE IF NOT EXISTS travel_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    destination VARCHAR(255) NOT NULL,
    travel_date DATE NOT NULL,
    return_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraints
ALTER TABLE bookings
ADD CONSTRAINT fk_booking_user
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Insert some sample destinations
INSERT INTO destinations (name, description, country, price, image_url, rating) VALUES
('Paris', 'The city of lights and romance', 'France', 1200.00, 'image/destinations/paris.jpg', 4.8),
('Tokyo', 'Experience the blend of traditional and modern', 'Japan', 1500.00, 'image/destinations/tokyo.jpg', 4.7),
('New York', 'The city that never sleeps', 'USA', 1100.00, 'image/destinations/new-york.jpg', 4.6),
('Rome', 'Explore ancient history and delicious cuisine', 'Italy', 950.00, 'image/destinations/rome.jpg', 4.5),
('Bali', 'Tropical paradise with beautiful beaches', 'Indonesia', 850.00, 'image/destinations/bali.jpg', 4.9); 