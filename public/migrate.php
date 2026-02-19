<?php
// public/migrate.php - TEMPORARY DB MIGRATION SCRIPT
require_once __DIR__ . '/../apps/backend/config/db.php';

echo "<h2>Starting Database Migration...</h2>";

try {
    // 1. Get the SQL content
    $sqlFile = __DIR__ . '/../db/schema_postgres.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // 2. Access the PDO instance from the global $db object
    // (In db.php, the $db variable is initialized at the end of the file)
    $pdo = $db->getPDO(); 
    
    // 3. Execute the SQL
    $pdo->exec($sql);
    
    echo "<p style='color:green;'>✅ SUCCESS: Tables created successfully!</p>";
    
    // 4. Seeding Initial Data (Optional but recommended for testing)
    echo "<h3>Seeding Initial Admin...</h3>";
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@rental.com', '$pass', 'admin') ON CONFLICT (email) DO NOTHING");
    echo "<p style='color:green;'>✅ Admin created (admin@rental.com / admin123)</p>";
    
    echo "<hr><p style='color:red;'>⚠️ IMPORTANT: Please delete this file (`migrate.php`) from your repository now for security.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}
?>
