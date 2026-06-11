-- Database Schema for DramaStream Platform
-- PHP 5.6 Compatible Streaming Platform
-- Run this SQL script to create the database structure

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS streaming_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE streaming_db;

-- Table: Users (Sistem Login)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Categories (Drama China, Anime, Movie, dll)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(50) NULL,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Shows (Menyimpan cache metadata dari API)
CREATE TABLE IF NOT EXISTS shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    api_show_id VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    synopsis TEXT,
    poster_url VARCHAR(500),
    backdrop_url VARCHAR(500) NULL,
    release_year INT,
    status ENUM('ongoing', 'completed', 'upcoming') DEFAULT 'ongoing',
    rating DECIMAL(3,2) NULL,
    total_episodes INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_api_show_id (api_show_id),
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Episodes (Optional - for storing episode data)
CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    episode_number INT NOT NULL,
    api_episode_id VARCHAR(100) NULL,
    title VARCHAR(255) NULL,
    synopsis TEXT NULL,
    thumbnail_url VARCHAR(500) NULL,
    duration INT NULL, -- in seconds
    air_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    INDEX idx_show_episode (show_id, episode_number),
    UNIQUE KEY unique_show_episode (show_id, episode_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Watch History (Untuk fitur "Lanjutkan Menonton")
CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    show_id INT NOT NULL,
    episode_id VARCHAR(100) NOT NULL,
    watch_progress INT DEFAULT 0, -- dalam detik
    completed BOOLEAN DEFAULT FALSE,
    last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    INDEX idx_user_show (user_id, show_id),
    INDEX idx_last_watched (last_watched)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: Watchlist/Bookmarks (Untuk fitur Bookmark)
CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    show_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_show (user_id, show_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('Drama China', 'drama-china', 'fas fa-tv', 1),
('Drama Korea', 'drama-korea', 'fas fa-film', 2),
('Anime', 'anime', 'fas fa-cartoon', 3),
('Movie', 'movie', 'fas fa-video', 4),
('Thai Drama', 'thai-drama', 'fas fa-play-circle', 5);

-- Insert default admin user (password: admin123)
-- Password hash generated using: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@dramastream.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Note: The password hash above is for 'admin123'
-- Please change the default password after first login!
