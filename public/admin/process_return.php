<?php
// admin/process_return.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $damageFine = (int)($_POST['damage_fine'] ?? 0);
    $shouldBlacklist = isset($_POST['blacklist_user']);
    
    $booking = $db->getBookingById($bookingId);
    if (!$booking) {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        exit;
    }

    // Auto-calculate late fee (₹200/hr)
    $endTs = strtotime($booking['end_date']);
    $nowTs = time();
    $lateHours = max(0, ceil(($nowTs - $endTs) / 3600));
    $lateFee = $lateHours * 200;

    $otp = $db->adminSetFines($bookingId, $damageFine, $lateFee, $shouldBlacklist);

    if ($otp) {
        echo json_encode([
            'success' => true, 
            'otp' => $otp,
            'late_fee' => $lateFee,
            'late_hours' => $lateHours
        ]);
    } else {
        error_log("Failed to set fines for booking $bookingId. Damage: $damageFine, Late: $lateFee");
        echo json_encode(['success' => false, 'error' => 'Failed to process return. Check backend logs.']);
    }
    exit;
}
