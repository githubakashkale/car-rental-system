# How to Run the Project Locally

This guide will help you set up and run the RentRide project on your own machine.

## 1. Prerequisites

Ensure you have the following installed:
- **PHP 8.x** or higher
- **Composer** (PHP dependency manager)
- **Web Browser**

## 2. Setup Instructions

### Step 1: Install Dependencies
Navigate to the backend directory and install the necessary PHP packages:
```bash
cd apps/backend
composer install
```

### Step 2: Configuration

#### Firebase Setup
The project requires Firebase Admin SDK for authentication.
1. Place your Firebase Service Account Key JSON file at:
   `apps/backend/config/key/firebase_admin_sdk.json`
2. If the folder `key` doesn't exist, create it.

#### Razorpay Setup
1. Open `apps/backend/config/razorpay.php`.
2. Replace the test keys with your own from the [Razorpay Dashboard](https://dashboard.razorpay.com/app/keys) if needed:
   ```php
   define('RAZORPAY_KEY_ID', 'your_key_id');
   define('RAZORPAY_KEY_SECRET', 'your_key_secret');
   ```

### Step 3: Database

The project uses a **PostgreSQL** database. 

1. Ensure your PostgreSQL server is running.
2. The database configuration is located in `apps/backend/config/db.php`.
3. If you need to initialize or seed the database, run:
   ```bash
   psql -U your_user -d rentride -f db/schema_postgres.sql
   php apps/backend/scripts/init_db.php
   ```

## 3. Running the Project

You can use the built-in PHP server to run the application.

1. Open a terminal in the project root.
2. Run the following command:
   ```bash
   php -S localhost:8000 -t apps/frontend
   ```
3. Open your browser and visit: `http://localhost:8000`

## 4. Default Credentials (for testing)

- **Admin Email:** `admin@rental.com`
- **Admin Password:** `admin123`

---
> [!NOTE]
> Ensure the `db/data.json` file has write permissions for the PHP process.
