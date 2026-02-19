<?php
// admin/booking_details.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /admin/dashboard.php");
    exit;
}

$bookings = $db->getAllBookings();
$booking = null;
foreach ($bookings as $b) {
    if ($b['id'] == $id) {
        $booking = $b;
        break;
    }
}

if (!$booking) {
    $_SESSION['flash_error'] = "Booking not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

$vehicle = $db->getVehicle($booking['vehicle_id']);
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>📑 Booking Details #<?= $booking['id'] ?></h1>
        <a href="/admin/dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Left: Customer & Vehicle -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">👤 Customer Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($booking['user_name']) ?></p>
                <p><strong>User ID:</strong> #<?= $booking['user_id'] ?></p>
                <p><strong>Mobile:</strong> <?= htmlspecialchars($booking['customer_phone'] ?? 'Not provided') ?></p>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">🚗 Vehicle Details</h3>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <img src="<?= $vehicle['image_url'] ?>" style="width: 100px; height: 60px; object-fit: cover; border-radius: 4px;">
                    <div>
                        <p><strong><?= htmlspecialchars($booking['vehicle_name']) ?></strong></p>
                        <p style="font-size: 0.85rem; color: var(--secondary);"><?= $vehicle['make'] ?> <?= $vehicle['model'] ?> (<?= $vehicle['year'] ?>)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Status & Payment -->
        <div>
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">💳 Payment & Dates</h3>
                <p><strong>Trip:</strong> <?= date('d M Y', strtotime($booking['start_date'])) ?> to <?= date('d M Y', strtotime($booking['end_date'])) ?></p>
                <p><strong>Total Price:</strong> <span style="font-size: 1.2rem; color: var(--primary); font-weight: 700;">₹<?= number_format($booking['total_price'], 2) ?></span></p>
                <p><strong>Security Deposit:</strong> ₹<?= number_format($booking['security_deposit'] ?? 5000, 2) ?></p>
                <p><strong>Payment Status:</strong> 
                    <span style="color: <?= ($booking['payment_status'] ?? 'pending') === 'paid' ? 'var(--success)' : '#ef4444' ?>; font-weight: 600;">
                        <?= strtoupper($booking['payment_status'] ?? 'pending') ?>
                    </span>
                </p>
                <?php if(isset($booking['payment_id'])): ?>
                    <p style="font-size: 0.8rem; color: var(--secondary);">Payment ID: <?= $booking['payment_id'] ?></p>
                <?php endif; ?>
            </div>

            <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">⚡ Quick Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php if($booking['booking_status'] === 'confirmed'): ?>
                        <button class="btn btn-primary">Mark as Completed</button>
                    <?php endif; ?>
                    <button class="btn btn-outline" style="color: #ef4444; border-color: #fecaca;">Cancel Booking</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
