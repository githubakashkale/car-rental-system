<?php
// templates/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RentRide — Premium vehicle rentals across India. Browse cars, book instantly, and enjoy transparent pricing with 24/7 support.">
    <meta property="og:title" content="RentRide | Premium Vehicle Rentals">
    <meta property="og:description" content="Find your perfect drive. Premium vehicles for every occasion.">
    <meta property="og:type" content="website">
    <title>RentRide | Premium Vehicle Rentals</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body style="visibility: hidden;"> <!-- Hide content until CSS loads -->
    <div id="page-loader" class="loader-wrapper">
        <div class="loader"></div>
    </div>
    <script>
        // Immediately show page but keep loader until all assets are ready
        document.body.style.visibility = "visible";
    </script>
    <header>
        <div class="container">
            <nav>
                <a href="/" class="logo">RentRide</a>
                
                <button class="mobile-menu-btn" onclick="document.querySelector('.nav-links').classList.toggle('active')">
                    ☰
                </button>

                <div class="nav-links">
                    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                        <a href="/">Browse Vehicles</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): 
                        // Fetch fresh points
                        $userPoints = 0;
                        if (isset($db)) {
                            $userPoints = $db->getUserPoints($_SESSION['user_id']);
                        } elseif (file_exists(__DIR__ . '/../backend/config/db.php')) {
                             require_once __DIR__ . '/../backend/config/db.php';
                             $userPoints = $db->getUserPoints($_SESSION['user_id']);
                        }
                    ?>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <span class="nav-user">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?></span>
                                <a href="/admin/dashboard.php">Admin Panel</a>
                            <?php else: ?>
                                <span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 99px; font-size: 0.9rem; font-weight: 600;">Points: <?= $userPoints ?></span>
                                <span style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 2px 8px; border-radius: 99px; font-size: 0.9rem; font-weight: 600;">Balance: ₹<?= number_format($db->getWalletBalance($_SESSION['user_id'])) ?></span>
                                <a href="/my-bookings.php">My Bookings</a>
                                <a href="/profile.php" class="nav-user">Hi, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?></a>
                            <?php endif; ?>
                            <a href="/logout.php" class="btn btn-outline btn-sm">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" style="font-weight: 600;">Login</a>
                        <a href="/register.php" class="btn btn-primary btn-sm">Get Started</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--radius); margin: 1rem 0;">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: var(--radius); margin: 1rem 0;">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
