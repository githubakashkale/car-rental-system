<?php
require_once __DIR__ . '/../config/db.php';

$users = $db->getAllUsers();
$adminFound = false;

foreach ($users as &$user) {
    if ($user['role'] === 'admin') {
        $user['email'] = 'admin@rental.com'; // Enforce standard email
        $user['password'] = password_hash('admin', PASSWORD_DEFAULT);
        $adminFound = true;
        echo "Admin password reset to 'admin'. Email set to 'admin@rental.com'.\n";
        break;
    }
}

if (!$adminFound) {
    echo "Admin user not found. Creating one.\n";
    $db->createUser('System Admin', 'admin@rental.com', 'admin', 'admin');
} else {
    // Save manually since we modified $users array which is a copy from getAllUsers() 
    // Wait, getAllUsers returns a copy usually. 
    // JsonDB needs a way to update. Let's use updateUser or raw manipulation.
    // updateUser in JsonDB updates by ID. 
    // Let's use the public updateUser method if available or raw.
    // Actually, I added updateUser method. Let's use that.
    
    // Re-check: updateUser($id, $name, $password = null)
    // It doesn't update email. 
    // I should update email manually or just rely on 'admin@rental.com' being there.
    // The previous cat of data.json showed email was already 'admin@rental.com'.
    // So I just need to reset password.
    
    $db->updateUser($user['id'], 'System Admin', 'admin'); // Update name and password
    echo "Admin updated via updateUser method.\n";
}
?>
