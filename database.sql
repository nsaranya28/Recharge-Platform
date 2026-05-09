-- Create Database
CREATE DATABASE IF NOT EXISTS recharge_db;
USE recharge_db;

-- Create plans table
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator ENUM('Jio', 'Airtel', 'VI') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    validity INT NOT NULL, -- in days
    data_per_day DECIMAL(5, 2) NOT NULL, -- in GB
    is_best_plan BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create sims table
CREATE TABLE IF NOT EXISTS sims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operator ENUM('Jio', 'Airtel', 'VI') NOT NULL,
    sim_type ENUM('Prepaid', 'Postpaid') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    validity INT NOT NULL, -- in days
    data_per_day DECIMAL(5, 2) NOT NULL, -- in GB
    is_best_sim BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert Sample Data
INSERT IGNORE INTO plans (operator, price, validity, data_per_day, is_best_plan) VALUES
('Jio', 239.00, 28, 1.5, TRUE),
('Jio', 299.00, 28, 2.0, FALSE),
('Jio', 666.00, 84, 1.5, FALSE),
('Jio', 749.00, 90, 2.0, FALSE),
('Airtel', 265.00, 28, 1.0, FALSE),
('Airtel', 299.00, 28, 1.5, TRUE),
('Airtel', 719.00, 84, 1.5, FALSE),
('Airtel', 839.00, 84, 2.0, FALSE),
('VI', 269.00, 28, 1.0, FALSE),
('VI', 299.00, 28, 1.5, FALSE),
('VI', 479.00, 56, 1.5, TRUE),
('VI', 719.00, 84, 1.5, FALSE),
-- Additional plans
('Jio', 899.00, 180, 3.0, FALSE),
('Airtel', 999.00, 180, 3.5, FALSE),
('VI', 1099.00, 180, 4.0, TRUE),
('Jio', 15.00, 1, 1.0, FALSE),
('Airtel', 19.00, 1, 1.0, FALSE);

-- Insert sample sims
INSERT IGNORE INTO sims (operator, sim_type, price, validity, data_per_day, is_best_sim) VALUES
('Jio', 'Prepaid', 199.00, 28, 1.5, FALSE),
('Airtel', 'Prepaid', 219.00, 28, 1.5, FALSE),
('VI', 'Prepaid', 229.00, 28, 1.5, FALSE),
('Jio', 'Postpaid', 599.00, 365, 5.0, TRUE),
('Airtel', 'Postpaid', 649.00, 365, 5.5, FALSE),
('VI', 'Postpaid', 699.00, 365, 6.0, FALSE);
-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    whatsapp VARCHAR(15),
    language ENUM('English', 'Tamil') DEFAULT 'English',
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create recharge_history table
CREATE TABLE IF NOT EXISTS recharge_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    mobile_number VARCHAR(15) NOT NULL,
    operator VARCHAR(50) NOT NULL,
    plan_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    recharge_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NOT NULL,
    status ENUM('Success', 'Failed', 'Pending') DEFAULT 'Success',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('Email', 'SMS', 'WhatsApp') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Sent', 'Failed') DEFAULT 'Sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create reminders table
CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recharge_id INT,
    reminder_type ENUM('3_days_before', '1_day_before', 'on_expiry') NOT NULL,
    scheduled_date DATE NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (recharge_id) REFERENCES recharge_history(id) ON DELETE CASCADE
);

-- Insert a sample user
INSERT IGNORE INTO users (name, email, mobile, whatsapp) VALUES 
('Test User', 'test@example.com', '1234567890', '1234567890');
