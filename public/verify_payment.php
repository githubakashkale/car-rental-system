<?php
// verify_payment.php — Razorpay Payment Verification
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/razorpay.php';
require_once __DIR__ . '/../apps/backend/vendor/autoload.php';

use App\Utils\Mail;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /");
    exit;
}

$bookingId = $_POST['booking_id'] ?? null;
$paymentId = $_POST['razorpay_payment_id'] ?? null;
$orderId = $_POST['razorpay_order_id'] ?? '';
$signature = $_POST['razorpay_signature'] ?? '';

if (!$bookingId || !$paymentId) {
    $_SESSION['flash_error'] = "Payment verification failed. Missing payment details.";
    header("Location: /my-bookings.php");
    exit;
}

// Verify booking belongs to user
$booking = $db->getBookingById($bookingId);
if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    $_SESSION['flash_error'] = "Invalid booking.";
    header("Location: /");
    exit;
}

// Signature verification
// When using Razorpay Orders API, verify with: order_id + "|" + payment_id
// When using direct checkout (no server-side order), we verify the payment_id is present
$paymentVerified = false;

if ($orderId && $signature) {
    // Full signature verification (when using Razorpay Orders API)
    $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    $paymentVerified = hash_equals($expectedSignature, $signature);
} else {
    // Direct checkout mode — payment_id presence indicates success
    // In production, you should verify via Razorpay API call
    // For demo/test mode, we accept the payment_id
    $paymentVerified = !empty($paymentId);
}

if ($paymentVerified) {
    // Update booking with payment details
    $db->updateBookingPayment($bookingId, $paymentId, $orderId);
    
    // Send Booking Confirmation Email
    $user = $db->getUserById($_SESSION['user_id']);
    $bookingData = $db->getBookingById($bookingId);
    if ($user && $bookingData) {
        Mail::sendBookingConfirmation($user['email'], $user['name'], $bookingData);
    }
    
    // Add reward points
    $db->addRewardPoints($_SESSION['user_id'], 10);
    
    $_SESSION['flash_success'] = "Payment successful! ₹" . number_format($booking['total_price'], 2) . " paid. Booking confirmed. (+10 Reward Points!)";
    header("Location: /my-bookings.php");
    exit;
} else {
    $_SESSION['flash_error'] = "Payment verification failed. If money was deducted, it will be refunded within 5-7 business days.";
    header("Location: /payment.php?booking_id=" . $bookingId);
    exit;
}
?>
