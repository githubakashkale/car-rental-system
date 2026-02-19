<?php
// public/set_admin.php - TEMPORARY SCRIPT TO SET ADMIN
require_once __DIR__ . '/../apps/backend/config/db.php';

// Change this to the email you want to make admin
$newAdminEmail = 'akashkale3762@gmail.com'; 
$defaultPassword = '123456'; // Password for new admin if created

echo "<h2>Updating Admin User...</h2>";

try {
    $user = $db->findUserByEmail($newAdminEmail);

    if ($user) {
        // Update existing user to admin
        $db->toggleUserStatus($user['id'], 'admin');
        echo "<p style='color:green;'>✅ SUCCESS: <b>{$user['name']}</b> ({$newAdminEmail}) role updated to <b>Admin</b>!</p>";
        
        if ($user['role'] === 'admin') {
            echo "<p>User was already an admin.</p>";
        }
    } else {
        // Create new admin user
        $newId = $db->createUser('System Admin', $newAdminEmail, password_hash($defaultPassword, PASSWORD_DEFAULT), 'admin');
        echo "<p style='color:green;'>✅ SUCCESS: Created new Admin user <b>$newAdminEmail</b> with password: <b>$defaultPassword</b></p>";
    }

    echo "<hr><p style='color:red;'>⚠️ IMPORTANT: Delete this file (`public/set_admin.php`) from GitHub after use if you don't want it public.</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

