<?php
// admin/dashboard.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
require_once __DIR__ . '/../../apps/backend/config/admin.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

// Extra safety: verify the logged-in user is still the designated admin
$adminUser = $db->getUserById($_SESSION['user_id']);
if (!$adminUser || !isAdminEmail($adminUser['email'])) {
    $_SESSION['role'] = 'user'; // Demote in session
    header("Location: /");
    exit;
}

$stats = $db->getStats();

// Handle Delete
if (isset($_POST['delete_vehicle'])) {
    $db->deleteVehicle($_POST['vehicle_id']);
    $_SESSION['flash_success'] = "Vehicle deleted successfully";
    header("Location: /admin/dashboard.php");
    exit;
}

// Handle Status Update
if (isset($_POST['update_booking'])) {
    $db->updateBookingStatus($_POST['booking_id'], $_POST['status']);
    header("Location: /admin/dashboard.php");
    exit;
}

// Handle Maintenance Toggle
if (isset($_POST['action']) && $_POST['action'] === 'toggle_maintenance') {
    $db->toggleMaintenance($_POST['vehicle_id']);
    $_SESSION['flash_success'] = "Vehicle maintenance status updated.";
    header("Location: /admin/dashboard.php");
    exit;
}

$vehicles = $db->getAllVehicles();
$bookings = $db->getAllBookings();

// Data for City-wise Analytics
$cityStats = [];
foreach ($bookings as $b) {
    if ($b['booking_status'] === 'cancelled') continue;
    $v = $db->getVehicle($b['vehicle_id']);
    $city = $v['location'] ?? 'Unknown';
    if (!isset($cityStats[$city])) {
        $cityStats[$city] = ['bookings' => 0, 'revenue' => 0];
    }
    $cityStats[$city]['bookings']++;
    $cityStats[$city]['revenue'] += $b['total_price'];
}
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <h1>Admin Dashboard</h1>
    
    <div class="dashboard-grid">
        <aside class="sidebar">
            <h3 style="margin-bottom: 1rem;">Menu</h3>
            <ul class="nav-links" style="flex-direction: column; gap: 0.5rem; align-items: flex-start;">
                <li><a href="/admin/dashboard.php" style="color: var(--primary); font-weight: bold;">Overview</a></li>
                <li><a href="/admin/users.php">User Management</a></li>
                <li><a href="/admin/revenue.php">Revenue & Finance</a></li>
                <li><a href="/admin/add_vehicle.php">Add New Vehicle</a></li>
                <li><a href="/admin/shops.php">Shop Management</a></li>
            </ul>
        </aside>
        
        <main>
            <!-- Stats -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
                <div class="stat-card">
                    <h3><?= $stats['vehicles'] ?></h3>
                    <p style="color: var(--secondary)">Vehicles</p>
                </div>
                <div class="stat-card">
                    <h3><?= $stats['bookings'] ?></h3>
                    <p style="color: var(--secondary)">Total Bookings</p>
                </div>
                <div class="stat-card">
                    <h3>₹<?= number_format($stats['revenue'], 2) ?></h3>
                    <p style="color: var(--secondary)">Total Revenue</p>
                </div>
            </div>

            <!-- Smart Analytics -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 1rem;">Revenue Analytics</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 1rem;">Vehicle Popularity</h3>
                    <canvas id="popularityChart"></canvas>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Prepare Data
                <?php
                // Revenue Data
                $revData = [0, 0, 0, 0, 0, $stats['revenue']]; 
                
                // Popularity Data
                // Popularity Data (Sorted)
                $popularity = [];
                foreach ($vehicles as $v) {
                    $count = 0;
                    foreach ($bookings as $b) {
                        if ((int)$b['vehicle_id'] === (int)$v['id']) $count++;
                    }
                    if ($count > 0) {
                        $popularity[] = ['name' => $v['vehicle_name'], 'count' => $count];
                    }
                }
                usort($popularity, fn($a, $b) => $b['count'] <=> $a['count']);
                $labels = array_column($popularity, 'name');
                $popData = array_column($popularity, 'count');
                ?>
                
                new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Revenue (₹)',
                            data: <?= json_encode($revData) ?>,
                            borderColor: '#4f46e5',
                            tension: 0.4
                        }]
                    }
                });

                // Vehicle Popularity Chart
                new Chart(document.getElementById('popularityChart'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($labels) ?>,
                        datasets: [{
                            label: 'Total Bookings',
                            data: <?= json_encode($popData) ?>,
                            backgroundColor: '#8b5cf6',
                            borderRadius: 5
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: { display: true, text: 'Most Booked Vehicles' }
                        },
                        scales: {
                            x: {
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            </script>

            <!-- Vehicles -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Vehicles</h2>
                <a href="/admin/add_vehicle.php" class="btn btn-primary btn-sm">Add Vehicle</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Price/Day</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td>#<?= $v['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($v['vehicle_name']) ?>
                                <span style="font-size:0.8rem; color:var(--text-light); display:block;"><?= htmlspecialchars($v['vehicle_type']) ?></span>
                            </td>
                            <td>
                                <?php 
                                $status = $v['availability_status'] ?? 'Available';
                                $statusColor = ($status === 'Available') ? '#16a34a' : (($status === 'Maintenance') ? '#f59e0b' : '#3b82f6');
                                ?>
                                <span style="font-size:0.75rem; background: <?= $statusColor ?>15; color: <?= $statusColor ?>; padding: 2px 8px; border-radius: 99px; font-weight: 600;">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td>₹<?= number_format($v['price_per_day'], 2) ?></td>
                            <td>
                                <?php $dh = is_string($v['damage_history'] ?? '[]') ? json_decode($v['damage_history'] ?? '[]', true) : ($v['damage_history'] ?? []); ?>
                                <button onclick='openDamageHistory(<?= json_encode($dh ?: []) ?>, "<?= htmlspecialchars($v['vehicle_name']) ?>")' class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-color: #64748b; color: #64748b;">History</button>
                                <a href="/admin/edit_vehicle.php?id=<?= $v['id'] ?>" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-color: #3b82f6; color: #3b82f6;">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.');">
                                    <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                                    <button type="submit" name="delete_vehicle" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Delete</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_maintenance">
                                    <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: <?= ($v['maintenance_status'] ?? false) ? '#f59e0b' : '#3b82f6' ?>; color: white;">
                                        <?= ($v['maintenance_status'] ?? false) ? 'End' : 'Maint.' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pending Returns -->
            <?php 
            $pendingReturns = array_filter($bookings, function($b) { return $b['booking_status'] === 'return_pending'; });
            if (!empty($pendingReturns)): ?>
            <h2 style="margin-top: 2rem; margin-bottom: 1rem; color: #92400e;">Pending Return Verifications</h2>
            <div class="table-container" style="border: 2px solid #fef3c7; background: #fffbeb; margin-bottom: 2rem;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Vehicle</th>
                            <th>Submitted</th>
                            <th>OTP Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReturns as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td><?= htmlspecialchars($b['user_name']) ?></td>
                            <td><?= htmlspecialchars($b['vehicle_name']) ?></td>
                            <?php $rr = is_string($b['return_request']) ? json_decode($b['return_request'], true) : $b['return_request']; ?>
                            <td><?= !empty($rr['requested_at']) ? date('M d, H:i', strtotime($rr['requested_at'])) : 'N/A' ?></td>
                            <td>
                                <?php if(!empty($b['approval_otp'])): ?>
                                    <span style="font-family: monospace; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 4px; border: 1px solid #166534;"><?= $b['approval_otp'] ?></span>
                                <?php else: ?>
                                    <span style="font-size: 0.7rem; color: #92400e; background: #fef3c7; padding: 2px 8px; border-radius: 4px;">Pending Generation</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/admin/verify_damage.php?id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" style="background: #d97706; border: none;">Verify & Set Fines</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Manage Bookings -->
            <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Manage All Bookings</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Vehicle</th>
                            <th>Dates</th>
                            <th>Delivery</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($b['user_name']) ?></strong>
                                <?php if(!empty($b['customer_phone'])): ?>
                                    <div style="font-size: 0.7rem; color: var(--secondary);">Phone: <?= htmlspecialchars($b['customer_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($b['vehicle_name']) ?></td>
                            <td>
                                <?= date('M d', strtotime($b['start_date'])) ?> - <?= date('M d', strtotime($b['end_date'])) ?>
                                <?php 
                                    $isOverdue = (strtotime($b['end_date']) < strtotime(date('Y-m-d'))) && in_array($b['booking_status'], ['confirmed', 'pending']);
                                    if($isOverdue):
                                ?>
                                    <div style="color: #dc2626; font-size: 0.65rem; font-weight: 700; margin-top: 2px;">OVERDUE</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $dm = $b['delivery_mode'] ?? 'pickup'; ?>
                                <?php if ($dm === 'home_delivery'): ?>
                                    <span style="font-size:0.75rem; background:#ede9fe; color:#7c3aed; padding:2px 8px; border-radius:99px;">Delivery</span>
                                    <?php if (!empty($b['delivery_address'])): ?>
                                        <div style="font-size:0.7rem; color:#475569; margin-top:4px; max-width:180px; line-height:1.3;">Address: <?= htmlspecialchars($b['delivery_address']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:99px;">Pickup</span>
                                    <?php 
                                    $shopInfo = $b['pickup_shop'] ?? '';
                                    if ($shopInfo) {
                                        $parts = explode('|', $shopInfo);
                                        $shopName = $parts[1] ?? '';
                                        $shopAddr = $parts[2] ?? '';
                                        $shopPhone = $parts[3] ?? '';
                                        $shopLat = $parts[4] ?? '';
                                        $shopLng = $parts[5] ?? '';
                                    ?>
                                        <div style="font-size:0.7rem; color:#475569; margin-top:4px; max-width:200px; line-height:1.4;">
                                            <strong><?= htmlspecialchars($shopName) ?></strong><br>
                                            Loc: <?= htmlspecialchars($shopAddr) ?>
                                            <?php if ($shopPhone): ?><br>Phone: <a href="tel:<?= str_replace(' ', '', $shopPhone) ?>" style="color:#4f46e5; text-decoration:none;"><?= htmlspecialchars($shopPhone) ?></a><?php endif; ?>
                                            <?php if ($shopLat && $shopLng): ?> &bull; <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $shopLat ?>,<?= $shopLng ?>" target="_blank" style="color:#16a34a; text-decoration:none; font-weight:600;">Navigate</a><?php endif; ?>
                                        </div>
                                    <?php } ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 99px; font-size: 0.75rem; 
                                    background: <?= $b['booking_status'] === 'confirmed' ? '#dcfce7' : ($b['booking_status'] === 'pending' ? '#fef9c3' : ($b['booking_status'] === 'payment_pending' ? '#fce7f3' : ($b['booking_status'] === 'completed' ? '#e0e7ff' : '#fee2e2'))) ?>;
                                    color: <?= $b['booking_status'] === 'confirmed' ? '#166534' : ($b['booking_status'] === 'pending' ? '#854d0e' : ($b['booking_status'] === 'payment_pending' ? '#9d174d' : ($b['booking_status'] === 'completed' ? '#4338ca' : '#991b1b'))) ?>;">
                                    <?= $b['booking_status'] === 'payment_pending' ? 'Awaiting Payment' : ucfirst($b['booking_status']) ?>
                                </span>
                            </td>
                             <td>
                                <a href="/admin/booking_details.php?id=<?= $b['id'] ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.4rem; border-color: #64748b; color: #64748b; margin-right: 4px;">Details</a>

                                <?php if ($b['booking_status'] === 'payment_pending'): ?>
                                    <span style="font-size:0.7rem; color:#9d174d;">Waiting for payment</span>
                                <?php endif; ?>

                                <?php if ($b['booking_status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button type="submit" name="update_booking" value="confirmed" class="btn btn-primary btn-sm" style="background: var(--success); font-size: 0.75rem; padding: 0.2rem 0.4rem;">✓</button>
                                    <input type="hidden" name="status" value="confirmed">
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <button type="submit" name="update_booking" value="cancelled" class="btn btn-danger btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;">✕</button>
                                    <input type="hidden" name="status" value="cancelled">
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($b['booking_status'] === 'confirmed'): ?>
                                    <span style="font-size:0.7rem; color:#6366f1;">Active Booking</span>
                                <?php endif; ?>
                                
                                <?php if($b['booking_status'] === 'completed'): ?>
                                    <span style="font-size:0.7rem; color:green;">Done (Penalty: ₹<?= $b['penalty'] ?? 0 ?>)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            </div>

            <script>
                function openVerifyModal(booking) {
                    console.log('Opening Verify Modal for booking:', booking.id);
                    const req = booking.return_request;
                    document.getElementById('verifyBookingId').value = booking.id;
                    document.getElementById('viewOdo').innerText = req.odometer;
                    document.getElementById('viewFuel').innerText = req.fuel_level;
                    document.getElementById('viewCond').innerText = req.condition;
                    document.getElementById('viewNotes').innerText = req.notes || 'No notes provided';
                    document.getElementById('viewPhoto').src = req.image_path || 'https://via.placeholder.com/300?text=No+Photo';
                    
                    // Load existing OTP/fines if present
                    const hasOtp = !!booking.approval_otp;
                    document.getElementById('otpDisplaySection').style.display = hasOtp ? 'block' : 'none';
                    document.getElementById('generatedOtp').innerText = booking.approval_otp || '------';
                    
                    document.getElementById('genOtpBtn').disabled = false;
                    document.getElementById('genOtpBtn').innerText = hasOtp ? 'Regenerate OTP' : 'Set Fines & Generate OTP';
                    document.getElementById('damageFine').value = booking.penalty || 0;
                    document.getElementById('blacklistToggle').checked = !!booking.should_blacklist;

                    // Calculate Late Fee estimate for display
                    const end = new Date(booking.end_date);
                    const now = new Date();
                    const diff = Math.max(0, now - end);
                    const hours = Math.ceil(diff / (1000 * 60 * 60));
                    
                    // Show saved late fee if available, else estimate
                    const lateFee = booking.late_fee !== undefined ? booking.late_fee : (hours * 200);
                    const lateHrs = booking.late_hours !== undefined ? booking.late_hours : hours;

                    document.getElementById('autoLateFee').innerText = `₹${lateFee}`;
                    document.getElementById('lateHoursText').innerText = `${lateHrs} hours late (₹200/hr)`;

                    document.getElementById('verifyModal').style.display = 'flex';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                function generateOTP(e) {
                    e.preventDefault();
                    const btn = document.getElementById('genOtpBtn');
                    const originalText = btn.innerText;
                    btn.disabled = true;
                    btn.innerText = 'Processing...';

                    const formData = new FormData(e.target);
                    if (document.getElementById('blacklistToggle').checked) {
                        formData.set('blacklist_user', '1');
                    }

                    fetch('/admin/process_return.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('otpDisplaySection').style.display = 'block';
                            document.getElementById('generatedOtp').innerText = data.otp;
                            btn.innerText = 'Regenerate OTP';
                            btn.disabled = false;
                            // Update late fee with actual backend calc
                            document.getElementById('autoLateFee').innerText = `₹${data.late_fee}`;
                            document.getElementById('lateHoursText').innerText = `${data.late_hours} hours late (₹200/hr)`;
                            
                            alert('OTP Generated Successfully!');
                        } else {
                            throw new Error(data.error || 'Failed to generate OTP');
                        }
                    })
                    .catch(err => {
                        console.error('OTP Generation Error:', err);
                        alert(err.message || 'System error. Check console.');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    });
                }

                function openDamageHistory(history, name) {
                    const list = document.getElementById('damageHistoryList');
                    document.getElementById('historyVehicleName').innerText = name;
                    list.innerHTML = '';
                    
                    if (!history || history.length === 0) {
                        list.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:2rem; color:var(--secondary);">No damage history recorded for this vehicle.</td></tr>';
                    } else {
                        history.forEach(item => {
                            const row = `<tr>
                                <td style="padding:0.75rem; border-bottom:1px solid #eee; font-size:0.85rem;">${item.date}</td>
                                <td style="padding:0.75rem; border-bottom:1px solid #eee; font-size:0.85rem;">${item.condition}</td>
                                <td style="padding:0.75rem; border-bottom:1px solid #eee; font-size:0.85rem; color:#991b1b; font-weight:600;">₹${item.penalty}</td>
                                <td style="padding:0.75rem; border-bottom:1px solid #eee; font-size:0.8rem; color:var(--secondary);">${item.notes || '-'}</td>
                            </tr>`;
                            list.innerHTML += row;
                        });
                    }
                    
                    document.getElementById('damageHistoryModal').style.display = 'flex';
                }
            </script>

            </div>

        </main>
    </div>
</div>

<!-- Return Verification Modal -->
<div id="verifyModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:2rem; border-radius:12px; max-width:600px; width:95%; max-height: 90vh; overflow-y: auto; position: relative; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <h2 style="margin:0;">Verify Vehicle Return</h2>
            <button onclick="document.getElementById('verifyModal').style.display='none'" style="border:none; background:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <h4 style="margin-bottom:0.5rem; color:var(--secondary);">User Submission</h4>
                <p style="font-size:0.9rem; margin-bottom:0.3rem;"><strong>Odometer:</strong> <span id="viewOdo"></span> km</p>
                <p style="font-size:0.9rem; margin-bottom:0.3rem;"><strong>Fuel:</strong> <span id="viewFuel"></span></p>
                <p style="font-size:0.9rem; margin-bottom:0.3rem;"><strong>Condition:</strong> <span id="viewCond"></span></p>
                <div style="margin-top:1rem;">
                    <strong>Notes:</strong>
                    <p id="viewNotes" style="font-size:0.85rem; color:#475569; background:#f8fafc; padding:0.5rem; border-radius:4px; margin-top:0.25rem;"></p>
                </div>
            </div>
            <div>
                <h4 style="margin-bottom:0.5rem; color:var(--secondary);">Condition Photo</h4>
                <img id="viewPhoto" src="" style="width:100%; border-radius:8px; border:1px solid #ddd; cursor:pointer;" onclick="window.open(this.src)">
            </div>
        </div>

        <div id="otpDisplaySection" style="display:none; background:#dcfce7; padding:1.5rem; border-radius:8px; text-align:center; margin-bottom:1.5rem; border:2px dashed #22c55e;">
            <span style="display:block; font-size:0.9rem; color:#166534; margin-bottom:0.5rem;">APPROVAL OTP GENERATED</span>
            <div id="generatedOtp" style="font-size:2.5rem; font-weight:800; letter-spacing:8px; color:#15803d;">------</div>
            <p style="font-size:0.8rem; color:#166534; margin-top:0.5rem;">Provide this OTP to the customer to finalize their return.</p>
        </div>

        <form id="verifyForm" onsubmit="generateOTP(event)">
            <input type="hidden" id="verifyBookingId" name="booking_id">
            
            <div style="background:#f1f5f9; padding:1rem; border-radius:8px; margin-bottom:1.5rem;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:0.3rem;">Late Fee (Automated)</label>
                        <div id="autoLateFee" style="font-weight:700; color:#991b1b;">₹0.00</div>
                        <small id="lateHoursText" style="color:var(--secondary); font-size:0.7rem;"></small>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:0.3rem;">Damage Fine (Manual)</label>
                        <input type="number" name="damage_fine" id="damageFine" value="0" style="width:100%; padding:0.4rem; border:1px solid #cbd5e1; border-radius:4px; font-weight:700;">
                        <small style="color:var(--secondary); font-size:0.7rem;">Based on photo verification</small>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:#991b1b; font-weight:600;">
                    <input type="checkbox" name="blacklist_user" id="blacklistToggle"> Blacklist Customer for Major Damage
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:1rem;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('verifyModal').style.display='none'">Close</button>
                <button type="submit" id="genOtpBtn" class="btn btn-primary" style="background:#4f46e5;">Set Fines & Generate OTP</button>
            </div>
        </form>
    </div>
</div>

<!-- Damage History Modal -->
<div id="damageHistoryModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9998; justify-content:center; align-items:center;">
    <div style="background:white; padding:2rem; border-radius:12px; max-width:700px; width:95%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <h2 style="margin:0;">Damage History: <span id="historyVehicleName" style="color:var(--primary);"></span></h2>
            <button onclick="document.getElementById('damageHistoryModal').style.display='none'" style="border:none; background:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:0.75rem; font-size:0.8rem; text-transform:uppercase; color:var(--secondary);">Date</th>
                    <th style="padding:0.75rem; font-size:0.8rem; text-transform:uppercase; color:var(--secondary);">Condition</th>
                    <th style="padding:0.75rem; font-size:0.8rem; text-transform:uppercase; color:var(--secondary);">Penalty</th>
                    <th style="padding:0.75rem; font-size:0.8rem; text-transform:uppercase; color:var(--secondary);">Notes</th>
                </tr>
            </thead>
            <tbody id="damageHistoryList">
            </tbody>
        </table>
        
        <div style="margin-top:2rem; text-align:right;">
            <button type="button" class="btn btn-primary" onclick="document.getElementById('damageHistoryModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
