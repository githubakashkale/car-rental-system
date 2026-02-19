<?php
// verify_otp.php - Verify submitted OTP (simulated)
session_start();
header('Content-Type: application/json');

$type = $_POST['type'] ?? ''; // 'email' or 'phone'
$otp = trim($_POST['otp'] ?? '');

if (!$type || !$otp) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Check attempts
$attemptsKey = "otp_{$type}_attempts";
$_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;

if ($_SESSION[$attemptsKey] > 5) {
    echo json_encode(['success' => false, 'error' => 'Too many attempts. Please request a new OTP.']);
    exit;
}

// Verify OTP
$storedOtp = $_SESSION["otp_{$type}"] ?? '';

if (!$storedOtp) {
    echo json_encode(['success' => false, 'error' => 'No OTP found. Please request one first.']);
    exit;
}

if ($otp === $storedOtp) {
    $_SESSION["otp_{$type}_verified"] = true;
    // Clean up
    unset($_SESSION["otp_{$type}"]);
    unset($_SESSION[$attemptsKey]);
    
    echo json_encode(['success' => true, 'message' => ucfirst($type) . ' verified successfully!']);
} else {
    $remaining = 5 - $_SESSION[$attemptsKey];
    echo json_encode(['success' => false, 'error' => "Invalid OTP. {$remaining} attempts remaining."]);
}
