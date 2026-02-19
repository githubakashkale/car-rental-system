<?php
// finalize_return.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $otp = trim($_POST['otp'] ?? '');

    if (!$bookingId || !$otp) {
        $_SESSION['flash_error'] = "Missing parameters.";
        header("Location: /my-bookings.php");
        exit;
    }

    $result = $db->finalizeReturn($bookingId, $otp);

    if ($result['success']) {
        $_SESSION['flash_success'] = "🎉 Return completed successfully! Your security deposit refund has been processed.";
        header("Location: /my-bookings.php");
    } else {
        $_SESSION['flash_error'] = "Error: " . $result['error'];
        header("Location: /my-bookings.php");
    }
    exit;
}

header("Location: /my-bookings.php");
exit;
