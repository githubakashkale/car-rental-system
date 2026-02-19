<?php
// admin/revenue.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

$bookings = $db->getAllBookings();
$vehicles = $db->getAllVehicles();

// Stats calculation
$totalRevenue = 0;
$pendingRevenue = 0;
$monthlyRevenue = array_fill(1, 12, 0);
$vehicleRevenue = [];

foreach ($bookings as $b) {
    if ($b['booking_status'] === 'cancelled') continue;
    
    $revenue = (float)$b['total_price'];
    
    if ($b['booking_status'] === 'completed' || $b['payment_status'] === 'paid') {
        $totalRevenue += $revenue;
        $month = (int)date('m', strtotime($b['created_at']));
        $monthlyRevenue[$month] += $revenue;
        
        $vId = $b['vehicle_id'];
        if (!isset($vehicleRevenue[$vId])) {
            $vehicleRevenue[$vId] = ['name' => $b['vehicle_name'], 'revenue' => 0, 'bookings' => 0];
        }
        $vehicleRevenue[$vId]['revenue'] += $revenue;
        $vehicleRevenue[$vId]['bookings']++;
    } else {
        $pendingRevenue += $revenue;
    }
}

// Sort vehicles by revenue descending
uasort($vehicleRevenue, fn($a, $b) => $b['revenue'] - $a['revenue']);
$topVehicles = array_slice($vehicleRevenue, 0, 5, true);

$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Revenue & Finance</h1>
        <a href="/admin/dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="border-top: 4px solid var(--success);">
            <p style="color: var(--secondary); font-size: 0.9rem;">Total Realized Revenue</p>
            <h2 style="color: #059669;">₹<?= number_format($totalRevenue, 2) ?></h2>
        </div>
        <div class="stat-card" style="border-top: 4px solid var(--primary);">
            <p style="color: var(--secondary); font-size: 0.9rem;">Average Booking Value</p>
            <?php $count = count($bookings); ?>
            <h2>₹<?= number_format($count > 0 ? $totalRevenue / $count : 0, 2) ?></h2>
        </div>
        <div class="stat-card" style="border-top: 4px solid #f59e0b;">
            <p style="color: var(--secondary); font-size: 0.9rem;">Pending / Unpaid Revenue</p>
            <h2 style="color: #d97706;">₹<?= number_format($pendingRevenue, 2) ?></h2>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h3>Monthly Revenue Growth</h3>
            <canvas id="monthlyChart" style="max-height: 350px;"></canvas>
        </div>
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h3>Top Earning Vehicles</h3>
            <div style="margin-top: 1rem;">
                <?php foreach ($topVehicles as $v): ?>
                    <div style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="font-weight: 600;"><?= htmlspecialchars($v['name']) ?></span>
                            <span style="color: var(--success); font-weight: 700;">₹<?= number_format($v['revenue']) ?></span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--secondary);"><?= $v['bookings'] ?> successful bookings</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($monthNames) ?>,
            datasets: [{
                label: 'Revenue (₹)',
                data: <?= json_encode(array_values($monthlyRevenue)) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
