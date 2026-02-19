<?php
require_once __DIR__ . '/../config/db.php';

$users = $db->getAllUsers();
$adminFound = false;

foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        // Update Admin
        $newEmail = 'akashkale3762@gmail.com';
        
        // Update basic info via method
        $db->updateUser($user['id'], 'System Admin', 'admin'); 
        
        // Update email manually since updateUser doesn't support it
        $stmt = $db->getPdo()->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$newEmail, $user['id']]);

        $adminFound = true;
        echo "Admin found. Password reset to 'admin'. Email updated to '$newEmail'.\n";
        break;
    }
}

if (!$adminFound) {
    echo "Admin user not found. Creating one.\n";
    $db->createUser('System Admin', 'akashkale3762@gmail.com', 'admin', 'admin');
    echo "Admin created with email 'akashkale3762@gmail.com' and password 'admin'.\n";
} else {
    echo "Admin update completed.\n";
}
?>
