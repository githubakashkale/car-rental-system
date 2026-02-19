<?php
// cancel_booking.php — Allows users to cancel pending/confirmed bookings
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = (int)$_POST['booking_id'];
    $booking = $db->getBookingById($bookingId);

    if (!$booking) {
        $_SESSION['flash_error'] = "Booking not found.";
        header("Location: /my-bookings.php");
        exit;
    }

    // Verify the booking belongs to this user
    if ($booking['user_id'] != $_SESSION['user_id']) {
        $_SESSION['flash_error'] = "Unauthorized action.";
        header("Location: /my-bookings.php");
        exit;
    }

    // Only allow cancellation for pending or confirmed bookings
    $status = $booking['booking_status'] ?? 'pending';
    if (!in_array($status, ['pending', 'confirmed', 'payment_pending'])) {
        $_SESSION['flash_error'] = "This booking cannot be cancelled.";
        header("Location: /my-bookings.php");
        exit;
    }

    // Cancel the booking and process refund
    $refundAmount = 0;
    $depositRefund = 0;

    // If payment was made (not payment_pending), refund the amount
    if ($status !== 'payment_pending') {
        $refundAmount = $booking['total_price'] ?? 0;
        $depositRefund = $booking['security_deposit'] ?? 5000;
    }

    // Use the proper cancelBooking method
    $db->cancelBooking($bookingId, $refundAmount, $depositRefund);
    $db->logActivity($_SESSION['user_id'], 'booking_cancelled', "Cancelled booking #$bookingId");

    if ($refundAmount > 0) {
        $totalRefund = $refundAmount + $depositRefund;
        $_SESSION['flash_success'] = "Booking #$bookingId cancelled. ₹" . number_format($totalRefund, 2) . " will be refunded to your original payment method within 5-7 business days.";
    } else {
        $_SESSION['flash_success'] = "Booking #$bookingId has been cancelled successfully. No payment was made, so no refund is needed.";
    }
    header("Location: /my-bookings.php");
    exit;
}

// If not POST, redirect
header("Location: /my-bookings.php");
exit;
?>
