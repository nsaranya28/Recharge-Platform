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
INSERT INTO plans (operator, price, validity, data_per_day, is_best_plan) VALUES
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
('Jio', 99.00, 1, 0.5, FALSE),
('Airtel', 99.00, 1, 0.5, FALSE);

-- Insert sample sims
INSERT INTO sims (operator, sim_type, price, validity, data_per_day, is_best_sim) VALUES
('Jio', 'Prepaid', 199.00, 28, 1.5, FALSE),
('Airtel', 'Prepaid', 219.00, 28, 1.5, FALSE),
('VI', 'Prepaid', 229.00, 28, 1.5, FALSE),
('Jio', 'Postpaid', 599.00, 365, 5.0, TRUE),
('Airtel', 'Postpaid', 649.00, 365, 5.5, FALSE),
('VI', 'Postpaid', 699.00, 365, 6.0, FALSE);
