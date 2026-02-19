<?php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Please login to view invoice.";
    header("Location: /login.php");
    exit;
}

$booking_id = $_GET['id'] ?? 0;
// Fetch booking functionality - reusing logic or just raw fetch for now since getBookingsByUser returns array
// Let's rely on a specific getBookingById if it exists, or fetch all and filter (inefficient but safe given current db structure)

$bookings = $db->getAllBookings();
$booking = null;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
foreach ($bookings as $b) {
    if ($b['id'] == $booking_id && ($b['user_id'] == $_SESSION['user_id'] || $isAdmin)) {
        $booking = $b;
        break;
    }
}

if (!$booking) {
    $_SESSION['flash_error'] = "Invoice not found or access denied.";
    header("Location: /");
    exit;
}

// Calculations
$startDate = new DateTime($booking['start_date']);
$endDate = new DateTime($booking['end_date']);
$days = $startDate->diff($endDate)->days + 1;

$pricePerDay = $booking['total_price'] / $days; // Roughly, assuming no driver
// Actually simple way: total_price in DB is the final rental cost.
// Let's assume inclusive of Tax for simplicity in display, or Exclusive?
// Let's make it look professional:
// Base Rental = Total / 1.18
// GST = Total - Base

$totalRental = $booking['total_price'];
$baseAmount = $totalRental / 1.18;
$gstAmount = $totalRental - $baseAmount;
$cgst = $gstAmount / 2;
$sgst = $gstAmount / 2;

$deposit = $booking['security_deposit'] ?? 5000;
$lateFee = $booking['late_fee'] ?? 0;
$penalty = $booking['penalty'] ?? 0;
$refund = $booking['final_refund'] ?? $deposit;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #INV-<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></title>
    <style>
        :root {
            --primary: #4f46e5;
            --gray: #6b7280;
            --light: #f3f4f6;
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px;
            color: #1f2937;
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: -1px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: 900;
            color: #111;
            margin: 0;
        }
        .meta {
            margin-top: 10px;
            font-size: 14px;
            color: var(--gray);
        }
        .bill-to {
            margin-bottom: 40px;
        }
        .bill-to h3 {
            font-size: 14px;
            text-transform: uppercase;
            color: var(--gray);
            margin-bottom: 8px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th {
            text-align: left;
            padding: 12px;
            background: var(--light);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
        }
        .totals {
            width: 300px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .row.grand-total {
            font-size: 18px;
            font-weight: 800;
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 10px;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: var(--gray);
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .paid { background: #dcfce7; color: #166534; }
        .pending { background: #fef9c3; color: #854d0e; }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="logo">RentRide</div>
            <div style="margin-top: 8px; font-size: 14px; color: var(--gray);">
                123 Premium Lane, Auto City<br>
                Maharashtra, India 400001<br>
                GSTIN: 27AABCU9603R1Z2
            </div>
        </div>
        <div class="invoice-details">
            <h1 class="invoice-title">INVOICE</h1>
            <div class="meta">#INV-<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></div>
            <div class="meta">Date: <?= date('d M Y') ?></div>
            <div style="margin-top: 10px;">
                <span class="badge <?= $booking['booking_status'] === 'confirmed' || $booking['booking_status'] === 'completed' ? 'paid' : 'pending' ?>">
                    <?= strtoupper($booking['booking_status']) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="bill-to">
        <h3>Bill To:</h3>
        <div style="font-weight: bold; font-size: 18px;"><?= htmlspecialchars($booking['user_name']) ?></div>
        <div style="color: var(--gray);">Premium Customer</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <b>Target Vehicle: <?= htmlspecialchars($booking['vehicle_name']) ?></b><br>
                    <span style="font-size: 12px; color: var(--gray);">
                        <?= $days ?> Days (<?= date('d M', strtotime($booking['start_date'])) ?> - <?= date('d M', strtotime($booking['end_date'])) ?>)
                    </span>
                </td>
                <td style="text-align: right;"><?= number_format($baseAmount, 2) ?></td>
            </tr>
            <tr>
                <td>CGST (9%)</td>
                <td style="text-align: right;"><?= number_format($cgst, 2) ?></td>
            </tr>
            <tr>
                <td>SGST (9%)</td>
                <td style="text-align: right;"><?= number_format($sgst, 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="totals">
            <div class="row">
                <span>Total Rental</span>
                <span>₹<?= number_format($totalRental, 2) ?></span>
            </div>
            <div class="row">
                <span>Security Deposit</span>
                <span>₹<?= number_format($deposit, 2) ?></span>
            </div>
            <div class="row grand-total">
                <span>Grand Total</span>
                <span>₹<?= number_format($totalRental + $deposit, 2) ?></span>
            </div>
        </div>
    </div>

    <?php if($booking['booking_status'] === 'completed'): ?>
    <div style="margin-top: 40px; padding: 20px; background: #fafafa; border: 1px dashed #ccc; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px;">Refund Statement</h3>
        <div class="totals" style="width: 100%; max-width: 400px; margin-left: auto;">
             <div class="row">
                <span>Security Deposit Paid</span>
                <span>₹<?= number_format($deposit, 2) ?></span>
            </div>
            <?php if($penalty > 0): ?>
            <div class="row" style="color: #ef4444;">
                <span>Damage Penalty</span>
                <span>- ₹<?= number_format($penalty, 2) ?></span>
            </div>
            <?php endif; ?>
            <?php if($lateFee > 0): ?>
            <div class="row" style="color: #ef4444;">
                <span>Late Fee (<?= $booking['late_hours'] ?> hrs)</span>
                <span>- ₹<?= number_format($lateFee, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="row grand-total" style="color: #166534; border-top-color: #166534;">
                <span>Refund Amount</span>
                <span>₹<?= number_format($refund, 2) ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p>Thank you for choosing RentRide Premium Services.<br>This is a computer-generated invoice and requires no signature.</p>
    </div>

    <a href="#" class="print-btn" onclick="window.print(); return false;">🖨️ Print / Download PDF</a>

</body>
</html>
