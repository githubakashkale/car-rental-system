<?php
// admin/verify_damage.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

$bookingId = $_GET['id'] ?? null;
$bookings = $db->getAllBookings();

// Filter for display
$pendingReturns = array_filter($bookings, function($b) { 
    return $b['booking_status'] === 'return_pending'; 
});

$activeBooking = null;
if ($bookingId) {
    foreach ($pendingReturns as $b) {
        if ($b['id'] == $bookingId) {
            $activeBooking = $b;
            break;
        }
    }
}

// Handle OTP Generation via POST (Same as modal logic but on its own page)
$msg = '';
$otpGenerated = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_otp'])) {
    $bId = (int)$_POST['booking_id'];
    $damageFine = (int)$_POST['damage_fine'];
    $shouldBlacklist = isset($_POST['blacklist_user']);
    
    $booking = $db->getBookingById($bId);
    if ($booking) {
        $endTs = strtotime($booking['end_date']);
        $nowTs = time();
        $lateHours = max(0, ceil(($nowTs - $endTs) / 3600));
        $lateFee = $lateHours * 200;

        $otpGenerated = $db->adminSetFines($bId, $damageFine, $lateFee, $shouldBlacklist);
        if ($otpGenerated) {
            $msg = "Success: OTP $otpGenerated generated!";
            // Refresh local data to show updated state
            $bookings = $db->getAllBookings();
            foreach ($bookings as $b) { 
                if ($b['id'] == $bId) { $activeBooking = $b; break; } 
            }
        } else {
            $msg = "Error: Failed to generate OTP.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Damage Verification | RentRide Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --error: #ef4444;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --secondary: #64748b;
            --radius: 0.75rem;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Outfit', sans-serif; background:var(--bg); color:var(--text); line-height:1.6; padding:2rem; }
        .container { max-width:1100px; margin:0 auto; }
        
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
        .btn { padding:0.6rem 1.2rem; border-radius:var(--radius); border:none; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; transition:0.2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-outline { border:1px solid #e2e8f0; background:white; color:var(--text); }
        
        .grid { display:grid; grid-template-columns: 350px 1fr; gap:2rem; }
        
        .card { background:var(--surface); padding:1.5rem; border-radius:var(--radius); box-shadow:var(--shadow); margin-bottom:1.5rem; }
        .card h3 { margin-bottom:1rem; border-bottom:1px solid #eee; padding-bottom:0.5rem; }
        
        .pending-list { list-style:none; }
        .pending-item { padding:1rem; border-bottom:1px solid #f1f5f9; border-radius:8px; transition:0.2s; cursor:pointer; }
        .pending-item:hover { background:#f1f5f9; }
        .pending-item.active { background:#e0e7ff; border-left:4px solid var(--primary); }
        .pending-item .id-badge { font-size:0.7rem; color:var(--secondary); }
        .pending-item .car-name { font-weight:700; display:block; }
        
        .detail-view { }
        .photo-box img { width:100%; border-radius:var(--radius); border:1px solid #ddd; }
        .stats-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:1rem; margin-bottom:2rem; }
        .stat-item { background:#f1f5f9; padding:1rem; border-radius:8px; text-align:center; }
        .stat-item span { display:block; font-size:0.8rem; color:var(--secondary); }
        .stat-item strong { font-size:1.2rem; }
        
        .otp-banner { background:#dcfce7; border:2px dashed #22c55e; padding:1.5rem; border-radius:12px; text-align:center; margin-bottom:2rem; }
        .otp-code { font-size:2.5rem; font-weight:800; letter-spacing:10px; color:#15803d; margin:1rem 0; }
        
        .form-group { margin-bottom:1.5rem; }
        label { display:block; font-weight:600; margin-bottom:0.5rem; }
        input[type="number"] { width:100%; padding:0.8rem; border:1px solid #cbd5e1; border-radius:8px; font-size:1.1rem; font-weight:700; }
        
        .alert { padding:1rem; border-radius:8px; margin-bottom:1.5rem; }
        .alert-success { background:#dcfce7; color:#166534; }
        .alert-error { background:#fee2e2; color:#991b1b; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1 style="font-size:1.75rem;">Damage Verification Center</h1>
            <p style="color:var(--secondary);">Process vehicle returns and assess damage fines</p>
        </div>
        <a href="/admin/dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
    </header>

    <?php if($msg): ?>
        <div class="alert <?= strpos($msg, 'Success') !== false ? 'alert-success' : 'alert-error' ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <!-- Sidebar List -->
        <aside>
            <div class="card" style="padding:1rem;">
                <h3 style="font-size:1rem;">Pending Submissions (<?= count($pendingReturns) ?>)</h3>
                <div class="pending-list">
                    <?php if(empty($pendingReturns)): ?>
                        <p style="padding:2rem; text-align:center; color:var(--secondary);">No returns pending</p>
                    <?php endif; ?>
                    <?php foreach($pendingReturns as $b): ?>
                        <div class="pending-item <?= $b['id'] == $bookingId ? 'active' : '' ?>" onclick="window.location.href='?id=<?= $b['id'] ?>'">
                            <span class="id-badge">Booking #<?= $b['id'] ?></span>
                            <span class="car-name"><?= htmlspecialchars($b['vehicle_name']) ?></span>
                            <span style="font-size:0.75rem; color:var(--secondary);">User: <?= htmlspecialchars($b['user_name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <!-- Detail Section -->
        <main class="detail-view">
            <?php if($activeBooking): 
                $req = is_string($activeBooking['return_request']) ? json_decode($activeBooking['return_request'], true) : $activeBooking['return_request'];
                if (!is_array($req)) $req = [];
            ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem;">
                        <div>
                            <h2><?= htmlspecialchars($activeBooking['vehicle_name']) ?></h2>
                            <p style="color:var(--secondary);">Rented by <?= htmlspecialchars($activeBooking['user_name']) ?> &bull; Return requested at <?= !empty($req['requested_at']) ? date('M d, H:i', strtotime($req['requested_at'])) : 'N/A' ?></p>
                        </div>
                        <div style="text-align:right;">
                            <span style="padding:4px 12px; background:#fef3c7; color:#92400e; border-radius:99px; font-weight:600; font-size:0.8rem;">PENDING VERIFICATION</span>
                        </div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <span>Odometer</span>
                            <strong><?= $req['odometer'] ?> km</strong>
                        </div>
                        <div class="stat-item">
                            <span>Fuel Level</span>
                            <strong><?= $req['fuel_level'] ?></strong>
                        </div>
                        <div class="stat-item">
                            <span>Condition</span>
                            <strong style="color:<?= strpos($req['condition'], 'Damage') !== false ? 'var(--error)' : 'var(--success)' ?>"><?= $req['condition'] ?></strong>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
                        <div class="photo-box">
                            <label>Submission Photo</label>
                            <img src="<?= $req['image_path'] ?>" alt="Vehicle Photo" onclick="window.open(this.src)">
                            <small style="display:block; margin-top:0.5rem; color:var(--secondary); text-align:center;">(Click to enlarge)</small>
                        </div>
                        
                        <div>
                            <label>User Notes</label>
                            <div style="background:#f8fafc; padding:1.5rem; border-radius:8px; border:1px solid #e2e8f0; min-height:100px; color:#475569;">
                                <?= nl2br(htmlspecialchars($req['notes'] ?: 'No notes provided by user.')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($activeBooking['approval_otp'])): ?>
                    <div class="otp-banner">
                        <span style="color:#166534; font-weight:600;">OTP ALREADY GENERATED</span>
                        <div class="otp-code"><?= $activeBooking['approval_otp'] ?></div>
                        <p style="font-size:0.9rem; color:#166534;">Final Fine Set: <strong>₹<?= $activeBooking['penalty'] ?></strong></p>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <h3>Final Assessment</h3>
                    <form method="POST">
                        <input type="hidden" name="booking_id" value="<?= $activeBooking['id'] ?>">
                        <div class="grid" style="grid-template-columns: 1fr 1fr;">
                            <div class="form-group">
                                <label>Damage Fine (₹)</label>
                                <input type="number" name="damage_fine" value="<?= $activeBooking['penalty'] ?? 0 ?>" required>
                                <small style="color:var(--secondary);">Manual fine based on damage severity</small>
                            </div>
                            <div class="form-group" style="display:flex; align-items:center;">
                                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; color:var(--error); margin-top:1.5rem;">
                                    <input type="checkbox" name="blacklist_user" style="width:20px; height:20px;" <?= ($activeBooking['should_blacklist'] ?? false) ? 'checked' : '' ?>>
                                    <strong>Blacklist Customer for Severe Misuse</strong>
                                </label>
                            </div>
                        </div>

                        <div style="background:#fefce8; padding:1.5rem; border-radius:8px; border:1px solid #fde047; margin-bottom:2rem;">
                            <h4 style="color:#854d0e; margin-bottom:0.5rem;">Late Fee Calculation (Automatic)</h4>
                            <?php
                                $end = strtotime($activeBooking['end_date']);
                                $now = time();
                                $hours = max(0, ceil(($now - $end) / 3600));
                                $lateFee = $hours * 200;
                            ?>
                            <div style="display:flex; justify-content:space-between; font-weight:600;">
                                <span>Time Overdue:</span>
                                <span><?= $hours ?> Hours</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.2rem; color:#991b1b; border-top:1px solid #fef3c7; margin-top:0.5rem; padding-top:0.5rem;">
                                <span>Late Fee:</span>
                                <span>₹<?= number_format($lateFee) ?></span>
                            </div>
                        </div>

                        <button type="submit" name="generate_otp" class="btn btn-primary" style="width:100%; padding:1rem; justify-content:center; font-size:1.1rem;">
                            <?= !empty($activeBooking['approval_otp']) ? '🔄 Regenerate Approval OTP' : '✅ Verify & Generate OTP' ?>
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <div class="card" style="text-align:center; padding:5rem 2rem; color:var(--secondary);">
                    <div style="font-size:4rem; margin-bottom:1rem;">🔍</div>
                    <h2>Please select a pending return from the list</h2>
                    <p>Submissions awaiting verification will appear on the left sidebar.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

</body>
</html>
