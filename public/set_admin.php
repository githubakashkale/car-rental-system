<?php
// public/set_admin.php - SET ADMIN: cars.rentride@gmail.com
// This script should be run once then deleted or protected.
require_once __DIR__ . '/../apps/backend/config/db.php';

// ONLY this email can be admin
$ADMIN_EMAIL = 'cars.rentride@gmail.com';
$defaultPassword = 'RentRide@Admin2024'; // Secure default password

echo "<h2>Setting Admin User...</h2>";

try {
    // Step 1: Demote ALL existing admins to 'user' role
    $pdo = $db->getPdo();
    $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE role = 'admin' AND LOWER(email) != LOWER(?)");
    $stmt->execute([$ADMIN_EMAIL]);
    $demotedCount = $stmt->rowCount();
    if ($demotedCount > 0) {
        echo "<p style='color:#d97706;'>⚠️ Demoted $demotedCount previous admin(s) to regular user.</p>";
    }

    // Step 2: Find or create the admin user
    $user = $db->findUserByEmail($ADMIN_EMAIL);

    if ($user) {
        // Update existing user to admin
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$user['id']]);
        echo "<p style='color:green;'>✅ SUCCESS: <b>{$user['name']}</b> ({$ADMIN_EMAIL}) is now the ONLY Admin!</p>";
    } else {
        // Create new admin user
        $newId = $db->createUser('RentRide Admin', $ADMIN_EMAIL, password_hash($defaultPassword, PASSWORD_DEFAULT), 'admin');
        echo "<p style='color:green;'>✅ SUCCESS: Created new Admin user <b>$ADMIN_EMAIL</b></p>";
        echo "<p style='color:#d97706;'>Default password: <b>$defaultPassword</b> — Change it immediately!</p>";
    }

    echo "<hr><p style='color:red;'>⚠️ IMPORTANT: Delete this file (<code>public/set_admin.php</code>) after use for security.</p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}
