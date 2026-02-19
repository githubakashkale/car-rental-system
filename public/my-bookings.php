<?php
// my-bookings.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookings = $db->getBookingsByUser($_SESSION['user_id']);
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="margin-top: 2rem;">
    <h1>My Bookings</h1>
    
    <?php if (count($bookings) === 0): ?>
        <div style="text-align: center; padding: 5rem 2rem; background: var(--surface); border-radius: var(--radius); margin-top: 1rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem; color: #e2e8f0;">(No Vehicle)</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--text);">No bookings yet</h3>
            <p style="color: var(--secondary); margin-bottom: 1.5rem; max-width: 360px; margin-left: auto; margin-right: auto;">
                You haven't made any bookings yet. Browse our premium collection and find your perfect ride!
            </p>
            <a href="/" class="btn btn-primary">🔍 Browse Vehicles</a>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 1.5rem; margin-top: 1.5rem;">
            <?php foreach ($bookings as $b): 
                $status = $b['booking_status'] ?? 'pending';
                $statusColors = [
                    'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534'],
                    'pending' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                    'completed' => ['bg' => '#e0e7ff', 'text' => '#4338ca'],
                    'payment_pending' => ['bg' => '#fce7f3', 'text' => '#9d174d'],
                    'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                ];
                $colors = $statusColors[$status] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                
                // Overdue Check
                $isOverdue = (strtotime($b['end_date']) < strtotime(date('Y-m-d'))) && in_array($status, ['confirmed', 'pending']);
            ?>
            <div class="interactive-card" style="display: flex; gap: 1.5rem; background: var(--surface); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); align-items: center; <?= $status === 'cancelled' ? 'opacity: 0.7;' : '' ?>" 
                 onclick="window.location.href='/booking_details.php?id=<?= $b['id'] ?>'">
                <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="Vehicle" style="width: 120px; height: 80px; object-fit: cover; border-radius: var(--radius);">
                
                <div style="flex: 1;">
                    <h3 style="margin-bottom: 0.25rem;"><?= htmlspecialchars($b['vehicle_name']) ?></h3>
                    <div style="color: var(--secondary); font-size: 0.875rem;">
                        <?= date('M d, Y', strtotime($b['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($b['end_date'])) ?>
                    </div>
                    <?php $delivery = $b['delivery_mode'] ?? 'pickup'; ?>
                    <div style="font-size: 0.75rem; margin-top: 0.25rem; color: <?= $delivery === 'home_delivery' ? '#7c3aed' : '#475569' ?>;">
                        <?php if ($delivery === 'home_delivery'): ?>
                            Home Delivery
                        <?php else: ?>
                            Shop: <?php
                            $shopInfo = $b['pickup_shop'] ?? '';
                            if ($shopInfo) {
                                $parts = explode('|', $shopInfo);
                                echo htmlspecialchars($parts[1] ?? 'Pickup from Shop');
                            } else {
                                echo 'Pickup from Shop';
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="text-align: right;" onclick="event.stopPropagation();">
                    <div style="font-weight: 700; margin-bottom: 0.25rem;">₹<?= number_format($b['total_price'], 2) ?></div>
                    <div style="font-size: 0.75rem; color: var(--secondary); margin-bottom: 0.5rem;">+ ₹<?= number_format($b['security_deposit'] ?? 5000) ?> Deposit</div>
                    
                    <span style="padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;
                        background: <?= $colors['bg'] ?>;
                        color: <?= $colors['text'] ?>;">
                        <?= $status === 'payment_pending' ? 'Awaiting Payment' : ucfirst($status) ?>
                    </span>
                    
                    <?php if($isOverdue): ?>
                        <div style="margin-top: 0.5rem; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; animation: pulse 2s infinite;">
                            OVERDUE: Return Required
                        </div>
                        <style>
                            @keyframes pulse {
                                0% { opacity: 1; }
                                50% { opacity: 0.6; }
                                100% { opacity: 1; }
                            }
                        </style>
                    <?php endif; ?>
                    
                    <?php if($status === 'payment_pending'): ?>
                        <br><a href="/payment.php?booking_id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:0.5rem; font-size:0.75rem; background: linear-gradient(135deg, #22c55e, #16a34a);">💳 Complete Payment</a>
                    <?php endif; ?>

                    <?php if(in_array($status, ['confirmed', 'completed'])): ?>
                        <br><a href="/invoice.php?id=<?= $b['id'] ?>" target="_blank" style="display:inline-block; margin-top:0.5rem; font-size:0.75rem; color:var(--primary); text-decoration:none;">View Invoice</a>
                    <?php endif; ?>

                    <?php if($status === 'confirmed'): ?>
                        <br><a href="/return_car.php?booking_id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" style="margin-top:0.5rem; font-size:0.75rem; background: #6366f1;">🔄 Return Car</a>
                    <?php endif; ?>

                    <?php if($status === 'return_pending'): ?>
                        <div style="margin-top: 1rem; padding: 1rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; text-align: left;">
                            <div style="font-weight: 600; color: #92400e; font-size: 0.8rem; margin-bottom: 0.5rem;">🕒 Return Verification Pending</div>
                            <?php if(!empty($b['approval_otp'])): ?>
                                <div style="font-size: 0.75rem; color: #b45309; margin-bottom: 1rem; border-bottom: 1px dashed #fef3c7; padding-bottom: 0.75rem;">
                                    <strong>Final Settlement Breakdown:</strong>
                                    <div style="display: flex; justify-content: space-between; margin-top: 0.25rem;">
                                        <span>Security Deposit</span>
                                        <span>₹<?= number_format($b['security_deposit'] ?? 5000) ?></span>
                                    </div>
                                    <?php if(($b['penalty'] ?? 0) > 0): ?>
                                    <div style="display: flex; justify-content: space-between; color: #dc2626;">
                                        <span>Damage Penalty</span>
                                        <span>- ₹<?= number_format($b['penalty']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(($b['late_fee'] ?? 0) > 0): ?>
                                    <div style="display: flex; justify-content: space-between; color: #dc2626;">
                                        <span>Late Fee (<?= $b['late_hours'] ?> hrs)</span>
                                        <span>- ₹<?= number_format($b['late_fee']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div style="display: flex; justify-content: space-between; margin-top: 0.25rem; font-weight: 700; color: #166534; border-top: 1px solid #fef3c7; padding-top: 0.25rem;">
                                        <span>Refund Amount</span>
                                        <span>₹<?= number_format(($b['security_deposit'] ?? 5000) - ($b['penalty'] ?? 0) - ($b['late_fee'] ?? 0)) ?></span>
                                    </div>
                                </div>
                                
                                <p style="font-size: 0.75rem; color: #b45309; margin-bottom: 0.75rem;">Please enter the OTP provided by the admin to accept these terms and finalize.</p>
                                <form action="/finalize_return.php" method="POST" style="display: flex; gap: 0.5rem;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="text" name="otp" maxlength="6" required placeholder="Enter 6-digit OTP" style="width: 140px; padding: 0.4rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 0.8rem;">
                                    <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.4rem 0.75rem;">Verify & Finish</button>
                                </form>
                            <?php else: ?>
                                <p style="font-size: 0.75rem; color: #b45309;">Waiting for admin to verify the car condition and photos. Once verified, you will be asked for an OTP.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(in_array($status, ['pending', 'confirmed', 'payment_pending'])): ?>
                        <br>
                        <form action="/cancel_booking.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="margin-top:0.5rem; font-size:0.75rem; background:#fee2e2; color:#991b1b; border:none; cursor:pointer;">✕ Cancel Booking</button>
                        </form>
                    <?php endif; ?>

                    <?php if($status === 'cancelled' && ($b['refund_amount'] ?? 0) > 0): ?>
                        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #166534; background: #dcfce7; padding: 4px 8px; border-radius: 4px;">
                            💰 Refund: ₹<?= number_format(($b['refund_amount'] ?? 0) + ($b['deposit_refund'] ?? 0), 2) ?>
                            <span style="display:block; font-size: 0.65rem; color: var(--secondary);">Processing to original payment method</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($status === 'completed'): ?>
                        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #166534; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">
                            Refunded: ₹<?= number_format($b['final_refund'] ?? 0) ?>
                            <?php if(($b['penalty'] ?? 0) > 0): ?>
                                <span style="display:block; color: #991b1b; font-size: 0.65rem;">(Penalty: -₹<?= $b['penalty'] ?>)</span>
                            <?php endif; ?>
                            <?php if(($b['late_fee'] ?? 0) > 0): ?>
                                <span style="display:block; color: #991b1b; font-size: 0.65rem;">(Late Fee: -₹<?= $b['late_fee'] ?> for <?= $b['late_hours'] ?> hrs)</span>
                            <?php endif; ?>
                        </div>
                        <?php if(!isset($b['review'])): ?>
                            <br><a href="/add_review.php?booking_id=<?= $b['id'] ?>" class="btn btn-outline btn-sm" style="margin-top:0.5rem; font-size:0.75rem; border-color: #f59e0b; color: #f59e0b;">⭐ Rate & Review</a>
                        <?php else: ?>
                            <div style="margin-top: 0.5rem; font-size: 0.7rem; color: var(--secondary);">✅ Review Submitted</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>

