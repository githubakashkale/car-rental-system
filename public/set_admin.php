<?php
// public/set_admin.php - TEMPORARY SCRIPT TO SET ADMIN
require_once __DIR__ . '/../apps/backend/config/db.php';

// Change this to the email you want to make admin
$newAdminEmail = 'akashkale3762@gmail.com'; 

echo "<h2>Updating Admin User...</h2>";

try {
    $pdo = $db->getPdo();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
    $stmt->execute([$newAdminEmail]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user to admin
        $update = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = ?");
        $update->execute([$newAdminEmail]);
        
        echo "<p style='color:green;'>✅ SUCCESS: <b>{$user['name']}</b> ({$newAdminEmail}) is now an Admin!</p>";
        echo "<p>Previous Role: {$user['role']}</p>";
    } else {
        echo "<p style='color:red;'>❌ ERROR: User with email <b>$newAdminEmail</b> not found.</p>";
        echo "<p>Please <a href='/register.php'>Register</a> this user first, then refresh this page.</p>";
    }

    echo "<hr><p style='color:red;'>⚠️ IMPORTANT: Delete this file (`public/set_admin.php`) from GitHub after use.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
?>
