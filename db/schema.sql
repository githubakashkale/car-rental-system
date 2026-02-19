-- db/schema.sql

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT CHECK(role IN ('admin', 'user')) NOT NULL DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles Table
CREATE TABLE IF NOT EXISTS vehicles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    make TEXT NOT NULL,
    model TEXT NOT NULL,
    year INTEGER NOT NULL,
    price_per_day REAL NOT NULL,
    image_url TEXT,
    description TEXT,
    available BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    vehicle_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price REAL NOT NULL,
    status TEXT CHECK(status IN ('pending', 'confirmed', 'cancelled', 'completed')) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Default Admin User (password: admin123)
-- Hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT OR IGNORE INTO users (name, email, password, role) 
VALUES ('System Admin', 'admin@rental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Vehicle Data
INSERT OR IGNORE INTO vehicles (make, model, year, price_per_day, image_url, description) VALUES 
('Toyota', 'Camry', 2024, 50.00, 'https://images.unsplash.com/photo-1621007947382-bb3c3968e3bb?auto=format&fit=crop&q=80&w=1000', 'Reliable and fuel efficient sedan.'),
('Tesla', 'Model 3', 2023, 120.00, 'https://images.unsplash.com/photo-1536700503339-1e4b06520771?auto=format&fit=crop&q=80&w=1000', 'Electric performance and style.'),
('Ford', 'Mustang', 2022, 150.00, 'https://images.unsplash.com/photo-1580273916550-e323be2ebcc6?auto=format&fit=crop&q=80&w=1000', 'Classic American muscle car.'),
('Honda', 'CR-V', 2024, 65.00, 'https://images.unsplash.com/photo-1568844293986-8d0400b6563a?auto=format&fit=crop&q=80&w=1000', 'Spacious SUV for family trips.');
