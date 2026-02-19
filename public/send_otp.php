<?php
// send_otp.php - Generate and "send" OTP (simulated for demo)
session_start();
require_once __DIR__ . '/../apps/backend/vendor/autoload.php';
use App\Utils\Mail;

header('Content-Type: application/json');

$type = $_POST['type'] ?? ''; // 'email' or 'phone'
$value = trim($_POST['value'] ?? '');

if (!$type || !$value) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Validate
if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

if ($type === 'phone' && !preg_match('/^[6-9]\d{9}$/', $value)) {
    echo json_encode(['success' => false, 'error' => 'Invalid phone number']);
    exit;
}

// Rate limiting - allow resend after 30 seconds
$lastSentKey = "otp_{$type}_sent_at";
if (isset($_SESSION[$lastSentKey]) && (time() - $_SESSION[$lastSentKey]) < 30) {
    $wait = 30 - (time() - $_SESSION[$lastSentKey]);
    echo json_encode(['success' => false, 'error' => "Please wait {$wait}s before resending"]);
    exit;
}

// Generate 6-digit OTP
$otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

// Store in session
$_SESSION["otp_{$type}"] = $otp;
$_SESSION["otp_{$type}_value"] = $value;
$_SESSION[$lastSentKey] = time();
$_SESSION["otp_{$type}_attempts"] = 0;

// In production, you would send SMS/email here.
if ($type === 'email') {
    $subject = "Your RentRide OTP Verification Code";
    $body = "<h2>Your OTP Code is: <b>{$otp}</b></h2><p>Please use this code to verify your email. Valid for 10 minutes.</p>";
    Mail::send($value, $subject, $body);
}

// For demo, we return the OTP to display on screen.
echo json_encode([
    'success' => true,
    'otp_hint' => $otp, // Remove this line in production
    'message' => $type === 'email' 
        ? "OTP sent to {$value}" 
        : "OTP sent to +91 {$value}"
]);
