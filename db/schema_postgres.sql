-- db/schema_postgres.sql

-- Drop existing tables if they exist
DROP TABLE IF EXISTS chat_history CASCADE;
DROP TABLE IF EXISTS activity_logs CASCADE;
DROP TABLE IF EXISTS bookings CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    phone TEXT,
    address TEXT,
    city TEXT,
    license_number TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    points INTEGER DEFAULT 0,
    wallet NUMERIC(10, 2) DEFAULT 0,
    favourites JSONB DEFAULT '[]',
    photo TEXT
);

-- Vehicles Table
CREATE TABLE vehicles (
    id SERIAL PRIMARY KEY,
    vehicle_name TEXT NOT NULL,
    vehicle_type TEXT,
    make TEXT,
    model TEXT,
    year INTEGER,
    price_per_day NUMERIC(10, 2) NOT NULL,
    description TEXT,
    image_url TEXT,
    location TEXT DEFAULT 'Mumbai',
    availability_status TEXT DEFAULT 'Available',
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    maintenance_status BOOLEAN DEFAULT FALSE,
    damage_history JSONB DEFAULT '[]'
);

-- Bookings Table
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    vehicle_id INTEGER REFERENCES vehicles(id) ON DELETE CASCADE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price NUMERIC(10, 2) NOT NULL,
    security_deposit NUMERIC(10, 2) DEFAULT 5000,
    delivery_mode TEXT DEFAULT 'pickup',
    delivery_address TEXT,
    pickup_shop TEXT,
    customer_phone TEXT,
    booking_status TEXT DEFAULT 'payment_pending',
    payment_status TEXT DEFAULT 'pending',
    payment_id TEXT,
    razorpay_order_id TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    refund_amount NUMERIC(10, 2) DEFAULT 0,
    deposit_refund NUMERIC(10, 2) DEFAULT 0,
    cancelled_at TIMESTAMPTZ,
    return_request JSONB DEFAULT '{}',
    penalty NUMERIC(10, 2) DEFAULT 0,
    late_fee NUMERIC(10, 2) DEFAULT 0,
    should_blacklist BOOLEAN DEFAULT FALSE,
    late_hours INTEGER DEFAULT 0,
    approval_otp TEXT,
    completed_at TIMESTAMPTZ,
    final_refund NUMERIC(10, 2) DEFAULT 0,
    review JSONB DEFAULT '{}'
);

-- Activity Logs Table
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    timestamp TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Chat History Table
CREATE TABLE chat_history (
    id TEXT PRIMARY KEY, -- Using TEXT for uniqid() compatibility
    user_id INTEGER,
    user_message TEXT,
    bot_reply TEXT,
    timestamp TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Indices for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_vehicle_id ON bookings(vehicle_id);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
