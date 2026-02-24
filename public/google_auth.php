<?php
// google_auth.php — Handle Google Sign-In/Sign-Up via Firebase
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/admin.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$photo = trim($_POST['photo'] ?? '');

if (!$email || !$name) {
    echo json_encode(['success' => false, 'error' => 'Missing user data from Google']);
    exit;
}

// Check if user already exists
$existingUser = $db->findUserByEmail($email);

if ($existingUser) {
    // Check blacklist status
    if (($existingUser['role'] ?? 'user') === 'blacklisted') {
        echo json_encode(['success' => false, 'error' => '🚫 Your account has been blacklisted due to major vehicle damage. Please contact support.']);
        exit;
    }

    // Enforce single admin: only cars.rentride@gmail.com can be admin
    $role = isAdminEmail($email) ? 'admin' : ($existingUser['role'] === 'admin' ? 'user' : $existingUser['role']);

    // User exists — log them in
    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['name'] = $existingUser['name'];
    $_SESSION['role'] = $role;

    // Update photo if available and not already set
    if ($photo && empty($existingUser['photo'])) {
        $db->updateUser($existingUser['id'], $existingUser['name'], null, null, null, null, null, $photo);
    }

    session_write_close(); // Ensure session is saved before redirect
    echo json_encode([
        'success' => true,
        'action' => 'login',
        'message' => 'Welcome back, ' . explode(' ', $existingUser['name'])[0] . '!',
        'redirect' => $role === 'admin' ? '/admin/dashboard.php' : '/'
    ]);
} else {
    // New user — create account (never auto-create as admin)
    $role = isAdminEmail($email) ? 'admin' : 'user';
    $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $userId = $db->createUser($name, $email, $randomPassword, $role, $phone);

    // Save Google profile photo
    if ($photo) {
        $db->updateUser($userId, $name, null, null, null, null, null, $photo);
    }

    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $role;
    
    session_write_close(); // Ensure session is saved before redirect
    echo json_encode([
        'success' => true,
        'action' => 'register',
        'message' => '🎉 Welcome to RentRide, ' . explode(' ', $name)[0] . '!',
        'redirect' => $role === 'admin' ? '/admin/dashboard.php' : '/'
    ]);
}

