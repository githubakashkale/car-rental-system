<?php
// booking_details.php — Full booking details page
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/shops.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingId = $_GET['id'] ?? null;
if (!$bookingId) {
    header("Location: /my-bookings.php");
    exit;
}

$booking = $db->getBookingById($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    $_SESSION['flash_error'] = "Booking not found.";
    header("Location: /my-bookings.php");
    exit;
}

$status = $booking['booking_status'] ?? 'pending';
$statusColors = [
    'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Confirmed'],
    'pending' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Pending'],
    'completed' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'label' => 'Completed'],
    'payment_pending' => ['bg' => '#fce7f3', 'text' => '#9d174d', 'label' => 'Awaiting Payment'],
    'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Cancelled'],
];
$colors = $statusColors[$status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst($status)];

$deliveryMode = $booking['delivery_mode'] ?? 'pickup';
$shopInfo = $booking['pickup_shop'] ?? '';
$shopParts = $shopInfo ? explode('|', $shopInfo) : [];
$deliveryAddress = $booking['delivery_address'] ?? '';

// Calculate days
$days = max(1, (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / 86400);

// Simulated driver info for home delivery (in production, this would come from the database)
$driverAssigned = in_array($status, ['confirmed', 'completed']);
$driverNames = ['Rajesh Kumar', 'Amit Sharma', 'Suresh Patel', 'Vikram Singh', 'Mahesh Gupta'];
$driverPhones = ['+91 98765 43210', '+91 98765 12345', '+91 87654 32109', '+91 97654 21098', '+91 96543 10987'];
$driverIdx = ($booking['id'] ?? 1) % count($driverNames);

// Find the nearest shop for home delivery (use first shop in the vehicle's city)
$vehicle = $db->getVehicle($booking['vehicle_id']);
$vehicleCity = $vehicle['location'] ?? 'Mumbai';
$nearestShop = ($SHOPS[$vehicleCity] ?? [])[0] ?? null;
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="max-width: 900px; margin-top: 2rem; margin-bottom: 3rem;">
    
    <!-- Back button -->
    <a href="/my-bookings.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--secondary); text-decoration: none; margin-bottom: 1.5rem; font-size: 0.9rem;">
        ← Back to My Bookings
    </a>

    <!-- Header Card -->
    <div style="background: var(--surface); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); margin-bottom: 1.5rem;">
        <div style="position: relative;">
            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Vehicle" style="width: 100%; height: 220px; object-fit: cover;">
            <div style="position: absolute; top: 16px; right: 16px;">
                <span style="padding: 0.4rem 1rem; border-radius: 99px; font-size: 0.8rem; font-weight: 700; background: <?= $colors['bg'] ?>; color: <?= $colors['text'] ?>; backdrop-filter: blur(8px);">
                    <?= $colors['label'] ?>
                </span>
            </div>
        </div>
        <div style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1 style="margin-bottom: 0.25rem; font-size: 1.5rem;"><?= htmlspecialchars($booking['vehicle_name']) ?></h1>
                    <div style="color: var(--secondary); font-size: 0.9rem;">
                        Booking #<?= $booking['id'] ?> • Created <?= date('M d, Y \a\t h:i A', strtotime($booking['created_at'])) ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">₹<?= number_format($booking['total_price'], 2) ?></div>
                    <div style="font-size: 0.8rem; color: var(--secondary);">+ ₹<?= number_format($booking['security_deposit'] ?? 5000) ?> deposit</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        
        <!-- Booking Timeline -->
        <div style="background: var(--surface); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">Booking Period</h3>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="flex: 1; background: #f0fdf4; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: #166534; text-transform: uppercase; font-weight: 600;">Pickup</div>
                    <div style="font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem;"><?= date('M d', strtotime($booking['start_date'])) ?></div>
                    <div style="font-size: 0.8rem; color: var(--secondary);"><?= date('l', strtotime($booking['start_date'])) ?></div>
                </div>
                <div style="font-size: 1.25rem; color: var(--secondary);">→</div>
                <div style="flex: 1; background: #fef2f2; padding: 1rem; border-radius: 0.5rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: #991b1b; text-transform: uppercase; font-weight: 600;">Return</div>
                    <div style="font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem;"><?= date('M d', strtotime($booking['end_date'])) ?></div>
                    <div style="font-size: 0.8rem; color: var(--secondary);"><?= date('l', strtotime($booking['end_date'])) ?></div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 0.75rem; font-size: 0.85rem; color: var(--secondary);">
                Duration: <strong><?= $days ?> day<?= $days > 1 ? 's' : '' ?></strong>
            </div>
        </div>

        <!-- Price Breakdown -->
        <div style="background: var(--surface); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">Price Breakdown</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--secondary);">Rental Cost</span>
                    <span style="font-weight: 600;">₹<?= number_format($booking['total_price'] - ($deliveryMode === 'home_delivery' ? 500 : 0), 2) ?></span>
                </div>
                <?php if ($deliveryMode === 'home_delivery'): ?>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--secondary);">Delivery Charge</span>
                    <span style="font-weight: 600;">₹500.00</span>
                </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; color: #16a34a;">
                    <span>Security Deposit (Refundable)</span>
                    <span style="font-weight: 600;">₹<?= number_format($booking['security_deposit'] ?? 5000, 2) ?></span>
                </div>
                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 0.25rem 0;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 700;">Total Paid</span>
                    <span style="font-weight: 700; color: var(--primary); font-size: 1.1rem;">₹<?= number_format($booking['total_price'] + ($booking['security_deposit'] ?? 5000), 2) ?></span>
                </div>
            </div>
            <?php if ($booking['payment_id'] ?? null): ?>
                <div style="margin-top: 0.75rem; font-size: 0.75rem; color: var(--secondary); background: #f8fafc; padding: 0.5rem; border-radius: 0.25rem;">
                    Payment ID: <?= htmlspecialchars($booking['payment_id']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delivery / Pickup Details — FULL WIDTH -->
    <div style="background: var(--surface); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow); margin-top: 1.5rem;">
        <?php if ($deliveryMode === 'home_delivery'): ?>
            <!-- HOME DELIVERY SECTION -->
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">Home Delivery Details</h3>
            
            <!-- Delivery Address -->
            <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #7c3aed; font-weight: 600; margin-bottom: 0.5rem;">Delivery Address</div>
                <div style="font-size: 1rem; font-weight: 500;">Loc: <?= htmlspecialchars($deliveryAddress ?: 'Not specified') ?></div>
            </div>

            <?php if ($driverAssigned): ?>
            <!-- Driver Info -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #16a34a; font-weight: 600; margin-bottom: 0.75rem;">Driver Assigned</div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #22c55e, #16a34a); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; font-weight: 700;">
                        <?= substr($driverNames[$driverIdx], 0, 1) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 1.05rem;"><?= $driverNames[$driverIdx] ?></div>
                        <div style="font-size: 0.85rem; color: var(--secondary); margin-top: 0.25rem;">
                            Phone: <a href="tel:<?= str_replace(' ', '', $driverPhones[$driverIdx]) ?>" style="color: #4f46e5; text-decoration: none; font-weight: 500;"><?= $driverPhones[$driverIdx] ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dispatching Shop -->
            <?php if ($nearestShop): ?>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1.25rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #2563eb; font-weight: 600; margin-bottom: 0.75rem;">Dispatching From</div>
                <div style="font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($nearestShop['name']) ?></div>
                <div style="font-size: 0.85rem; color: var(--secondary);">Loc: <?= htmlspecialchars($nearestShop['address']) ?></div>
                <div style="display: flex; gap: 1rem; margin-top: 0.75rem; flex-wrap: wrap;">
                    <a href="tel:<?= str_replace(' ', '', $nearestShop['phone']) ?>" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; color: #4f46e5; text-decoration: none; font-weight: 500;">
                        Phone: <?= $nearestShop['phone'] ?>
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $nearestShop['lat'] ?>,<?= $nearestShop['lng'] ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.85rem; color: #16a34a; text-decoration: none; font-weight: 600;">
                        Navigate on Google Maps
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 0.75rem; padding: 1.25rem;">
                <div style="font-size: 0.85rem; color: #854d0e;">
                    ⏳ Driver details will be updated once your booking is confirmed. You will see the driver's name, contact number, and the shop from where the car will be dispatched.
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- PICKUP FROM SHOP SECTION -->
            <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">Shop Details</h3>
            
            <?php if (count($shopParts) >= 3): ?>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem;">
                <div style="font-weight: 700; font-size: 1.15rem; margin-bottom: 0.5rem; color: var(--text);">
                    <?= htmlspecialchars($shopParts[1]) ?>
                </div>
                <div style="font-size: 0.9rem; color: var(--secondary); margin-bottom: 1rem;">
                    Loc: <?= htmlspecialchars($shopParts[2]) ?>
                </div>
                
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <?php if (isset($shopParts[3]) && $shopParts[3]): ?>
                    <a href="tel:<?= str_replace(' ', '', $shopParts[3]) ?>" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.2rem; background: #eef2ff; color: #4f46e5; border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: background 0.2s;">
                        📞 <?= htmlspecialchars($shopParts[3]) ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isset($shopParts[4]) && isset($shopParts[5]) && $shopParts[4] && $shopParts[5]): ?>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $shopParts[4] ?>,<?= $shopParts[5] ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.2rem; background: #f0fdf4; color: #16a34a; border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: background 0.2s;">
                        Navigate on Google Maps
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Embedded Map -->
                <?php if (isset($shopParts[4]) && isset($shopParts[5]) && $shopParts[4] && $shopParts[5]): ?>
                <div style="margin-top: 1rem; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e2e8f0;">
                    <iframe 
                        src="https://www.google.com/maps?q=<?= $shopParts[4] ?>,<?= $shopParts[5] ?>&output=embed" 
                        width="100%" height="200" style="border:0;" 
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 0.75rem; padding: 1rem; font-size: 0.85rem; color: #854d0e;">
                Shop details not available for this booking.
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
        <?php if ($status === 'payment_pending'): ?>
            <a href="/payment.php?booking_id=<?= $booking['id'] ?>" class="btn btn-primary" style="background: linear-gradient(135deg, #22c55e, #16a34a);">Complete Payment</a>
        <?php endif; ?>
        
        <?php if (in_array($status, ['confirmed', 'completed'])): ?>
            <a href="/invoice.php?id=<?= $booking['id'] ?>" target="_blank" class="btn btn-primary" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">View Invoice</a>
        <?php endif; ?>
        
        <?php if (in_array($status, ['pending', 'confirmed', 'payment_pending'])): ?>
            <form action="/cancel_booking.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                <button type="submit" class="btn" style="background: #fee2e2; color: #991b1b; border: none; cursor: pointer;">✕ Cancel Booking</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Refund Info -->
    <?php if ($status === 'cancelled' && ($booking['refund_amount'] ?? 0) > 0): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius); padding: 1.25rem; margin-top: 1.5rem;">
        <h4 style="color: #166534; margin-bottom: 0.5rem;">Refund Details</h4>
        <div style="display: flex; gap: 2rem;">
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Rental Refund</div>
                <div style="font-weight: 600;">₹<?= number_format($booking['refund_amount'] ?? 0, 2) ?></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Deposit Refund</div>
                <div style="font-weight: 600;">₹<?= number_format($booking['deposit_refund'] ?? 0, 2) ?></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Total Refund</div>
                <div style="font-weight: 700; color: #166534; font-size: 1.1rem;">₹<?= number_format(($booking['refund_amount'] ?? 0) + ($booking['deposit_refund'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div style="font-size: 0.75rem; color: var(--secondary); margin-top: 0.5rem;">Processing to original payment method within 5-7 business days.</div>
    </div>
    <?php endif; ?>

    <?php if ($status === 'completed'): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius); padding: 1.25rem; margin-top: 1.5rem;">
        <h4 style="color: #166534; margin-bottom: 0.5rem;">Trip Completed</h4>
        <div style="display: flex; gap: 2rem;">
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Deposit Refunded</div>
                <div style="font-weight: 600;">₹<?= number_format($booking['final_refund'] ?? 0, 2) ?></div>
            </div>
            <?php if (($booking['penalty'] ?? 0) > 0): ?>
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Penalty Deducted</div>
                <div style="font-weight: 600; color: #991b1b;">-₹<?= number_format($booking['penalty'] ?? 0, 2) ?></div>
            </div>
            <?php endif; ?>
            <?php if (($booking['late_fee'] ?? 0) > 0): ?>
            <div>
                <div style="font-size: 0.8rem; color: var(--secondary);">Late Fee (<?= $booking['late_hours'] ?>hrs)</div>
                <div style="font-weight: 600; color: #991b1b;">-₹<?= number_format($booking['late_fee'] ?? 0, 2) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
