-- File: C:\xampp\htdocs\appF\includes\db\schema.sql

-- Create database
CREATE DATABASE IF NOT EXISTS fitcoach_pro;
USE fitcoach_pro;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'coach', 'editor', 'client') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    profile_image VARCHAR(255),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- User tokens (for remember me)
CREATE TABLE user_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password resets
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity logs
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Coaches and clients relationship
CREATE TABLE coaches_clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coach_id INT NOT NULL,
    client_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coach_client (coach_id, client_id)
);

-- Editor assignments
CREATE TABLE editor_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    editor_id INT NOT NULL,
    client_id INT NOT NULL,
    coach_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (editor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_editor_client (editor_id, client_id)
);

-- Leads from public website
CREATE TABLE leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    fitness_goal TEXT,
    package_id INT,
    message TEXT,
    status ENUM('new', 'contacted', 'converted', 'rejected') DEFAULT 'new',
    converted_to_client_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Exercise library
CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    muscle_group VARCHAR(50),
    equipment VARCHAR(50),
    description TEXT,
    media_url VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Workout templates
CREATE TABLE workout_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    training_type ENUM('strength', 'hypertrophy', 'endurance', 'power') NOT NULL,
    description TEXT,
    coach_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Template exercises
CREATE TABLE workout_template_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets INT NOT NULL,
    reps VARCHAR(50) NOT NULL, -- Can be "8-12" or "AMRAP"
    rest_time INT, -- in seconds
    intensity VARCHAR(50), -- e.g., "70% 1RM"
    exercise_order INT NOT NULL,
    FOREIGN KEY (template_id) REFERENCES workout_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Client workouts (assigned)
CREATE TABLE client_workouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    template_id INT,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('scheduled', 'in_progress', 'completed', 'missed') DEFAULT 'scheduled',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES workout_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Client workout exercises (actual logs)
CREATE TABLE client_workout_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_workout_id INT NOT NULL,
    exercise_id INT NOT NULL,
    set_number INT NOT NULL,
    reps INT,
    weight DECIMAL(5,2),
    completed BOOLEAN DEFAULT FALSE,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_workout_id) REFERENCES client_workouts(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

-- Nutrition - Food database
CREATE TABLE foods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    calories_per_100g DECIMAL(6,2),
    protein_per_100g DECIMAL(5,2),
    carbs_per_100g DECIMAL(5,2),
    fat_per_100g DECIMAL(5,2),
    is_custom BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Meal logs
CREATE TABLE meal_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
    meal_date DATE NOT NULL,
    total_calories DECIMAL(7,2),
    total_protein DECIMAL(5,2),
    total_carbs DECIMAL(5,2),
    total_fat DECIMAL(5,2),
    notes TEXT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Meal items
CREATE TABLE meal_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    meal_log_id INT NOT NULL,
    food_id INT,
    custom_food_name VARCHAR(100),
    quantity_grams DECIMAL(6,2),
    cooking_method VARCHAR(50),
    calories DECIMAL(7,2),
    protein DECIMAL(5,2),
    carbs DECIMAL(5,2),
    fat DECIMAL(5,2),
    FOREIGN KEY (meal_log_id) REFERENCES meal_logs(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE SET NULL
);

-- Body measurements
CREATE TABLE body_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    measurement_date DATE NOT NULL,
    weight DECIMAL(5,2),
    waist DECIMAL(5,2),
    chest DECIMAL(5,2),
    hips DECIMAL(5,2),
    arms DECIMAL(5,2),
    thighs DECIMAL(5,2),
    body_fat_us_navy DECIMAL(4,1),
    body_fat_caliper DECIMAL(4,1),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_date (client_id, measurement_date)
);

-- Progress photos
CREATE TABLE progress_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    photo_date DATE NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    body_part VARCHAR(50),
    comments TEXT,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Fitness tests
CREATE TABLE fitness_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    test_type VARCHAR(50) NOT NULL, -- e.g., '1RM_bench', 'cooper_run'
    test_date DATE NOT NULL,
    result DECIMAL(8,2) NOT NULL,
    unit VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Packages
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coach_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    benefits TEXT,
    price DECIMAL(10,2) NOT NULL,
    payment_type ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'monthly',
    color_theme VARCHAR(20),
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Payments
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    package_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    due_date DATE,
    status ENUM('paid', 'pending', 'overdue', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);

-- Leaderboard
CREATE TABLE leaderboard (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    metric_type VARCHAR(50) NOT NULL, -- 'strength', 'endurance', 'consistency'
    score INT NOT NULL,
    ranking_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_client_metric_date (client_id, metric_type, ranking_date)
);

-- Notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'workout_reminder', 'payment_due', 'comment'
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Site settings (for admin)
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'image', 'color', 'boolean') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin@123)
INSERT INTO users (name, email, password_hash, role) VALUES 
('Admin User', 'admin@fitcoach.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample coach
INSERT INTO users (name, email, password_hash, role) VALUES 
('Sarah Chen', 'sarah@fitcoach.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach');

-- Insert sample client
INSERT INTO users (name, email, password_hash, role) VALUES 
('Michael Wong', 'michael@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client');

-- Insert sample editor
INSERT INTO users (name, email, password_hash, role) VALUES 
('Editor Smith', 'editor@fitcoach.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor');

-- Assign client to coach
INSERT INTO coaches_clients (coach_id, client_id) VALUES (2, 3);

-- Insert sample exercises
INSERT INTO exercises (name, muscle_group, equipment, description) VALUES
('Bench Press', 'Chest', 'Barbell', 'Lie on bench, lower bar to chest, press up'),
('Squat', 'Legs', 'Barbell', 'Stand with bar on back, lower hips, stand up'),
('Deadlift', 'Back', 'Barbell', 'Lift bar from ground, stand straight'),
('Pull-ups', 'Back', 'Bodyweight', 'Pull chin over bar');

-- Insert sample settings
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'FitCoach Pro', 'text'),
('primary_color', '#4A6FA5', 'color'),
('logo_url', '/assets/images/logo.png', 'image');