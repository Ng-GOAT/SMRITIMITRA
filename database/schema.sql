CREATE DATABASE IF NOT EXISTS smritimitra;
USE smritimitra;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient','caregiver') DEFAULT 'patient',
    age INT,
    dob DATE,
    preferred_language VARCHAR(50) DEFAULT 'English',
    avatar VARCHAR(10) DEFAULT '👵',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Caregiver-Patient link
CREATE TABLE caregiver_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caregiver_id INT NOT NULL,
    patient_id INT NOT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medicines
CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    dosage VARCHAR(100),
    time_slot ENUM('morning','afternoon','evening','night') NOT NULL,
    time_hour INT NOT NULL,
    time_minute INT DEFAULT 0,
    period ENUM('AM','PM') NOT NULL,
    status ENUM('taken','pending','missed') DEFAULT 'pending',
    taken_at DATETIME DEFAULT NULL,
    reminder_enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Memories
CREATE TABLE memories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category ENUM('childhood','education','family','celebrations','other') DEFAULT 'other',
    photo_url VARCHAR(500) DEFAULT NULL,
    is_favorite TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Game scores
CREATE TABLE game_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type ENUM('memory_match','number_sequence','pattern_recognition','routine_challenge') NOT NULL,
    score INT DEFAULT 0,
    level_reached INT DEFAULT 1,
    difficulty ENUM('easy','medium','hard') DEFAULT 'easy',
    accuracy DECIMAL(5,2) DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Daily activity log
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('game','medicine','memory','companion','reminder') NOT NULL,
    description VARCHAR(255),
    completed TINYINT(1) DEFAULT 0,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reminders (medicine, hydration, activity, appointment)
CREATE TABLE reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reminder_type ENUM('medicine','hydration','activity','appointment') NOT NULL,
    title VARCHAR(200) NOT NULL,
    reminder_time DATETIME NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    is_recurring TINYINT(1) DEFAULT 0,
    recurrence_pattern VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings
CREATE TABLE user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    medicine_reminders TINYINT(1) DEFAULT 1,
    activity_reminders TINYINT(1) DEFAULT 1,
    hydration_reminders TINYINT(1) DEFAULT 1,
    appointment_reminders TINYINT(1) DEFAULT 1,
    voice_companion TINYINT(1) DEFAULT 1,
    large_text TINYINT(1) DEFAULT 0,
    dark_mode TINYINT(1) DEFAULT 0,
    preferred_language VARCHAR(50) DEFAULT 'English',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
