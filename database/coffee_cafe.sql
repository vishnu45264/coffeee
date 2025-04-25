-- Create database
CREATE DATABASE IF NOT EXISTS coffee_cafe;
USE coffee_cafe;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  price DECIMAL(6,2) NOT NULL,
  image VARCHAR(255),
  category VARCHAR(50) NOT NULL,
  active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  total_price DECIMAL(8,2) NOT NULL,
  order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(50) DEFAULT 'pending',
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(item_id) REFERENCES menu_items(id)
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  feedback_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@coffeecafe.com', 'vishnu45.', 'admin');
-- Default password is 'admin123'

-- Insert some sample menu items
INSERT INTO menu_items (name, description, price, image, category) VALUES
('Espresso', 'Strong and concentrated coffee with rich flavor.', 2.99, 'espresso.jpg', 'Hot Coffee'),
('Cappuccino', 'Espresso with steamed milk and thick foam.', 3.99, 'cappuccino.jpg', 'Hot Coffee'),
('Latte', 'Espresso with steamed milk and a small layer of foam.', 4.49, 'latte.jpg', 'Hot Coffee'),
('Americano', 'Espresso diluted with hot water.', 3.49, 'americano.jpg', 'Hot Coffee'),
('Iced Coffee', 'Chilled coffee served with ice cubes.', 3.99, 'iced-coffee.jpg', 'Cold Coffee'),
('Caramel Frappuccino', 'Blended coffee with caramel syrup and whipped cream.', 5.49, 'caramel-frappuccino.jpg', 'Cold Coffee'),
('Chocolate Muffin', 'Rich chocolate muffin with chocolate chips.', 2.99, 'chocolate-muffin.jpg', 'Pastries'),
('Croissant', 'Buttery, flaky pastry.', 2.49, 'croissant.jpg', 'Pastries');