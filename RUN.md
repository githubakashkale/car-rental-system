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

#### Environment Variables
The project uses environment variables for database and API keys. You can create a `.env` file (ignored by Git) or set them in your terminal:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
- `OPENAI_API_KEY`, `RAZORPAY_KEY_ID`, etc.

#### Firebase Setup
1. Place your Firebase Service Account Key JSON file at:
   `apps/backend/config/key/firebase_admin_sdk.json`

### Step 3: Database

The project uses a **PostgreSQL** database. 

1. Ensure your PostgreSQL server is running.
2. The database configuration is located in `apps/backend/config/db.php`.
3. If you need to initialize or seed the database, run:
   ```bash
   psql -U your_user -d rentride -f db/schema_postgres.sql
   ```

## 3. Running the Project

You can use the built-in PHP server to run the application.

1. Open a terminal in the project root.
2. Run the following command:
   ```bash
   php -S localhost:8000 -t public
   ```
3. Open your browser and visit: `http://localhost:8000`

## 4. Default Credentials (for testing)

- **Admin Email:** `admin@rental.com`
- **Admin Password:** `admin123`
