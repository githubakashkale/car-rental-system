<?php
// profile.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user = $db->getUserById($_SESSION['user_id']);

if (!$user) {
    echo "User not found.";
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = trim($_POST['name'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if (!$newName) {
        $error = "Name is required.";
    } else {
        $photo = null;
        
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
            
            if (in_array($mime, $allowed)) {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/assets/uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $photo = '/assets/uploads/profiles/' . $filename;
                }
            } else {
                $error = "Invalid image format. Use JPG, PNG, WebP, or GIF.";
            }
        }
        
        if (!$error) {
            $db->updateUser($_SESSION['user_id'], $newName, $newPassword ?: null, null, null, null, null, $photo);
            $_SESSION['name'] = $newName;
            $success = "Profile updated successfully!";
            $user = $db->getUserById($_SESSION['user_id']);
        }
    }
}

$bookings = $db->getBookingsByUser($_SESSION['user_id']);
$totalBookings = count($bookings);
$totalSpent = 0;
foreach ($bookings as $b) {
    if (($b['payment_status'] ?? '') === 'paid' || ($b['booking_status'] ?? '') !== 'payment_pending') {
        $totalSpent += $b['total_price'] ?? 0;
    }
}
$memberSince = $user['created_at'] ?? 'N/A';
$userPoints = $user['points'] ?? 0;
$walletBalance = $db->getWalletBalance($_SESSION['user_id']);
$favourites = $db->getFavourites($_SESSION['user_id']);

// Generate a referral code from user ID
$referralCode = 'RENT' . strtoupper(substr(md5($_SESSION['user_id'] . 'rentride'), 0, 6));
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<style>
.profile-container {
    max-width: 600px;
    margin: 1.5rem auto 3rem;
}

/* Profile Header Card */
.profile-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #8b5cf6 100%);
    border-radius: 1.5rem;
    padding: 2.5rem 2rem 2rem;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.profile-hero::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.profile-hero::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -30px;
    width: 160px;
    height: 160px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    border: 4px solid rgba(255,255,255,0.4);
    margin: 0 auto 1rem;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    position: relative;
    z-index: 1;
}

.profile-email {
    opacity: 0.85;
    font-size: 0.9rem;
    position: relative;
    z-index: 1;
}

.profile-member {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    opacity: 0.7;
    position: relative;
    z-index: 1;
}

.profile-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.15);
    padding: 0.5rem 1rem;
    border-radius: 99px;
    margin-top: 0.75rem;
    font-size: 0.9rem;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(5px);
}

/* Quick Stats */
.profile-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.stat-tile {
    background: var(--surface);
    border-radius: 1rem;
    padding: 1.25rem;
    text-align: center;
    box-shadow: var(--shadow);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.stat-tile:hover {
    transform: translateY(-2px);
}

.stat-tile:active {
    transform: scale(0.96) translateY(0);
}

.stat-tile .stat-icon {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.stat-tile .stat-value {
    font-size: 1.3rem;
    font-weight: 700;
}

.stat-tile .stat-label {
    font-size: 0.75rem;
    color: var(--text-light);
    margin-top: 0.1rem;
}

.stat-tile.points { border-top: 3px solid #f59e0b; }
.stat-tile.points .stat-value { color: #f59e0b; }
.stat-tile.wallet { border-top: 3px solid #22c55e; }
.stat-tile.wallet .stat-value { color: #22c55e; }

/* Profile Menu Items */
.profile-menu {
    background: var(--surface);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
}

.menu-section-title {
    padding: 1rem 1.25rem 0.5rem;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-light);
    font-weight: 600;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: var(--text);
}

.menu-item:last-child {
    border-bottom: none;
}

.menu-item:hover {
    background: #f8fafc;
}

.menu-item:active {
    transform: scale(0.98);
    background: #f1f5f9;
}

.menu-item-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.menu-item-content {
    flex: 1;
}

.menu-item-title {
    font-weight: 600;
    font-size: 0.95rem;
}

.menu-item-sub {
    font-size: 0.75rem;
    color: var(--text-light);
    margin-top: 1px;
}

.menu-item-arrow {
    color: #cbd5e1;
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* Expandable Sections */
.expandable-content {
    display: none;
    padding: 0 1.25rem 1.25rem;
    animation: slideDown 0.3s ease;
}

.expandable-content.active {
    display: block;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Offers */
.offer-card {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-left: 4px solid #f59e0b;
}

.offer-card h4 {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    color: #92400e;
}

.offer-card p {
    font-size: 0.8rem;
    color: #78350f;
}

/* Referral */
.referral-code-box {
    background: #f1f5f9;
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
    margin-bottom: 0.75rem;
}

.referral-code {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: 0.15em;
    color: var(--primary);
    font-family: monospace;
}

/* Favourite Cars */
.fav-car-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.fav-car-item:last-child { border-bottom: none; }

.fav-car-img {
    width: 60px;
    height: 45px;
    border-radius: 0.5rem;
    object-fit: cover;
}

.fav-car-info { flex: 1; }

.fav-car-name {
    font-weight: 600;
    font-size: 0.9rem;
}

.fav-car-price {
    font-size: 0.75rem;
    color: var(--primary);
    font-weight: 600;
}

.fav-remove-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #ef4444;
    transition: transform 0.2s;
}

.fav-remove-btn:hover {
    transform: scale(1.2);
}

/* Wallet Detail */
.wallet-balance-card {
    background: linear-gradient(135deg, #065f46, #059669);
    border-radius: 1rem;
    padding: 1.5rem;
    color: white;
    text-align: center;
    margin-bottom: 1rem;
}

.wallet-balance-amount {
    font-size: 2rem;
    font-weight: 800;
}

.wallet-balance-label {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-top: 0.25rem;
}

/* Rating Stars */
.star-rating {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin: 1rem 0;
}

.star-rating .star {
    font-size: 2rem;
    cursor: pointer;
    color: #e2e8f0;
    transition: color 0.2s, transform 0.2s;
}

.star-rating .star:hover,
.star-rating .star.active {
    color: #f59e0b;
    transform: scale(1.15);
}

/* Settings Form */
.settings-form .form-group {
    margin-bottom: 1rem;
}

.settings-form .form-group label {
    display: block;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 0.35rem;
}

.settings-form .form-control {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    font-family: inherit;
    transition: border-color 0.2s;
}

.settings-form .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Mobile Adjustments */
@media (max-width: 640px) {
    .profile-container { margin: 1rem auto; padding: 0 0.5rem; }
    .profile-hero { padding: 2rem 1.5rem 1.5rem; border-radius: 1rem; }
    .profile-avatar { width: 80px; height: 80px; font-size: 2rem; }
    .profile-stats { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="profile-container">

    <!-- Profile Hero Card -->
    <div class="profile-hero">
        <div class="profile-avatar">
            <?php if (!empty($user['photo'])): ?>
                <img src="<?= htmlspecialchars($user['photo']) ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="profile-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
        <div class="profile-member">Member since <?= date('M Y', strtotime($memberSince)) ?></div>
        <?php if (!empty($user['phone'])): ?>
            <div class="profile-phone" style="background: white; border: 1px solid #ddd; color: #444;">
                <span>Phone: <?= htmlspecialchars($user['phone'] ?? 'Update Phone') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Points & Wallet Stats -->
    <?php if ($user['role'] === 'user'): ?>
    <div class="profile-stats">
        <div class="stat-tile points">
            <div class="stat-icon" style="color: #f59e0b;">Points</div>
            <div class="stat-value"><?= $userPoints ?></div>
            <div class="stat-label">Reward Points</div>
        </div>
        <div class="stat-tile wallet">
            <div class="stat-icon" style="color: #10b981;">Wallet</div>
            <div class="stat-value">₹<?= number_format($walletBalance) ?></div>
            <div class="stat-label">Wallet Balance</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Menu Section: Rewards (Users Only) -->
    <?php if ($user['role'] === 'user'): ?>
    <div class="profile-menu">
        <div class="menu-section-title">Rewards & Savings</div>

        <!-- Offers -->
        <div class="menu-item" onclick="toggleSection('offers')">
            <div class="menu-item-icon" style="background: #fef3c7; color: #f59e0b;">🎁</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Offers</div>
                <div class="menu-item-sub">Exclusive deals for you</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="offers">
            <div class="offer-card">
                <h4>🎉 First Ride 20% Off</h4>
                <p>Use code <strong>FIRST20</strong> on your next booking. Max discount ₹500.</p>
            </div>
            <div class="offer-card">
                <h4>🔥 Weekend Special</h4>
                <p>Get 15% off on weekend bookings (Sat-Sun). Auto-applied!</p>
            </div>
            <div class="offer-card" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); border-left-color: #3b82f6;">
                <h4 style="color: #1e40af;">💎 Loyalty Bonus</h4>
                <p style="color: #1e3a5f;">Complete 5 bookings to unlock ₹1000 wallet credit!</p>
            </div>
        </div>

        <!-- Refer & Earn -->
        <div class="menu-item" onclick="toggleSection('referral')">
            <div class="menu-item-icon" style="background: #e0e7ff; color: #4f46e5;">🤝</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Refer & Earn</div>
                <div class="menu-item-sub">Earn ₹200 per referral</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="referral">
            <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 0.75rem;">Share your referral code with friends. When they complete their first booking, you both get <strong>₹200</strong> wallet credit!</p>
            <div class="referral-code-box">
                <div style="font-size: 0.75rem; color: var(--text-light); margin-bottom: 0.25rem;">Your Referral Code</div>
                <div class="referral-code" id="refCode"><?= $referralCode ?></div>
            </div>
            <button onclick="copyReferral()" class="btn btn-primary" style="width: 100%; font-size: 0.9rem; margin-bottom: 1.5rem;">📋 Copy & Share Code</button>
            <div id="copyMsg" style="display:none; text-align:center; margin-top:0.5rem; font-size:0.8rem; color: var(--success); font-weight:600;">✅ Copied to clipboard!</div>

            <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <p style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Have a code? Enter it here:</p>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="promoInput" class="form-control" placeholder="Enter referral or promo code" style="padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; flex: 1; font-size: 0.85rem;">
                    <button onclick="applyPromo()" class="btn btn-primary btn-sm">Apply</button>
                </div>
                <div id="promoMsg" style="display:none; margin-top:0.5rem; font-size:0.8rem; font-weight:600;"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Menu Section: My Stuff (Users Only) -->
    <?php if ($user['role'] === 'user'): ?>
    <div class="profile-menu">
        <div class="menu-section-title">My Stuff</div>

        <!-- Favourite Cars -->
        <div class="menu-item" onclick="toggleSection('favourites')">
            <div class="menu-item-icon" style="background: #fce7f3; color: #ec4899;">❤️</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Favourite Cars</div>
                <div class="menu-item-sub"><?= count($favourites) ?> vehicle<?= count($favourites) !== 1 ? 's' : '' ?> saved</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="favourites">
            <?php if (empty($favourites)): ?>
                <div style="text-align: center; padding: 1.5rem 0; color: var(--text-light);">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">💔</div>
                    <p style="font-size: 0.85rem;">No favourites yet. Browse vehicles and tap ❤️ to save!</p>
                    <a href="/" class="btn btn-outline btn-sm" style="margin-top: 0.75rem;">Browse Vehicles</a>
                </div>
            <?php else: ?>
                <?php foreach ($favourites as $fav): ?>
                <div class="fav-car-item" id="fav-<?= $fav['id'] ?>">
                    <img src="<?= htmlspecialchars($fav['image_url']) ?>" alt="<?= htmlspecialchars($fav['vehicle_name']) ?>" class="fav-car-img">
                    <div class="fav-car-info">
                        <div class="fav-car-name"><?= htmlspecialchars($fav['vehicle_name']) ?></div>
                        <div class="fav-car-price">₹<?= number_format($fav['price_per_day']) ?>/day</div>
                    </div>
                    <button class="fav-remove-btn" onclick="removeFavourite(<?= $fav['id'] ?>)" title="Remove">✕</button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Wallet -->
        <div class="menu-item" onclick="toggleSection('wallet')">
            <div class="menu-item-icon" style="background: #dcfce7; color: #22c55e;">💳</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Wallet</div>
                <div class="menu-item-sub">Balance: ₹<?= number_format($walletBalance) ?></div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="wallet">
            <div class="wallet-balance-card">
                <div style="font-size: 0.8rem; opacity: 0.8; margin-bottom: 0.25rem;">Available Balance</div>
                <div class="wallet-balance-amount">₹<?= number_format($walletBalance) ?></div>
                <div class="wallet-balance-label">Use wallet balance during checkout</div>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-light); text-align: center;">
                <p>Earn wallet credits through referrals, offers, and loyalty rewards.</p>
                <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow); border: 1px solid #f1f5f9;">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="flex: 1;">
                            <h4 style="margin-bottom: 0.25rem;">Referral Code</h4>
                            <div id="referCode" style="font-family: monospace; font-size: 1.2rem; font-weight: 700; color: #4f46e5; margin-bottom: 0.75rem; letter-spacing: 1px;"><?= $referralCode ?></div>
                            <p style="font-size: 0.8rem; color: var(--text-light); margin-bottom: 1rem;">Share this code with friends. You both get rewards when they book!</p>
                            <button onclick="copyReferral('<?= $referralCode ?>')" class="btn btn-outline btn-sm">Copy Code</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Menu Section: More -->
    <div class="profile-menu">
        <div class="menu-section-title">More</div>

        <!-- Policies -->
        <a href="/privacy-policy.php" class="menu-item">
            <div class="menu-item-icon" style="background: #f1f5f9; color: #64748b;">🔒</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Privacy Policy</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </a>
        <a href="/terms-of-service.php" class="menu-item">
            <div class="menu-item-icon" style="background: #f1f5f9; color: #64748b;">📜</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Terms of Service</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </a>
        <a href="/refund-policy.php" class="menu-item">
            <div class="menu-item-icon" style="background: #f1f5f9; color: #64748b;">💸</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Refund Policy</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </a>

        <?php if ($user['role'] === 'user'): ?>
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
            <a href="/my-bookings.php" class="menu-item">
                <div class="menu-label">My Bookings</div>
                <span style="font-size: 1.2rem; color: #cbd5e1;">&rarr;</span>
            </a>
            <a href="#offers" class="menu-item">
                <div class="menu-label">Offers & Rewards</div>
                <span style="font-size: 1.2rem; color: #cbd5e1;">&rarr;</span>
            </a>
            <a href="https://wa.me/911234567890" class="menu-item" target="_blank">
                <div class="menu-label">Support Chat</div>
                <span style="font-size: 1.2rem; color: #cbd5e1;">&rarr;</span>
            </a>
            <a href="#rate" class="menu-item">
                <div class="menu-label">Rate RentRide</div>
                <span style="font-size: 1.2rem; color: #cbd5e1;">&rarr;</span>
            </a>
        </div>
        <?php endif; ?>
        <!-- Rate Us -->
        <div class="menu-item" onclick="toggleSection('rateus')">
            <div class="menu-item-icon" style="background: #fef9c3; color: #eab308;">⭐</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Rate Us</div>
                <div class="menu-item-sub">Share your experience</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="rateus">
            <p style="text-align: center; font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.5rem;">How would you rate RentRide?</p>
            <div class="star-rating" id="starRating">
                <span class="star" data-value="1" onclick="rateApp(1)">★</span>
                <span class="star" data-value="2" onclick="rateApp(2)">★</span>
                <span class="star" data-value="3" onclick="rateApp(3)">★</span>
                <span class="star" data-value="4" onclick="rateApp(4)">★</span>
                <span class="star" data-value="5" onclick="rateApp(5)">★</span>
            </div>
            <div id="rateMsg" style="display:none; text-align:center; font-size:0.85rem; color: var(--success); font-weight:600;"></div>
        </div>

        <!-- Settings -->
        <div class="menu-item" onclick="toggleSection('settings')">
            <div class="menu-item-icon" style="background: #f1f5f9; color: #475569;">⚙️</div>
            <div class="menu-item-content">
                <div class="menu-item-title">Settings</div>
                <div class="menu-item-sub">Edit profile, password & more</div>
            </div>
            <span class="menu-item-arrow">›</span>
        </div>
        <div class="expandable-content" id="settings">
            <form method="POST" class="settings-form" enctype="multipart/form-data">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group" style="text-align: center;">
                    <label>Profile Photo</label>
                    <div style="margin: 0.5rem 0;">
                        <?php if (!empty($user['photo'])): ?>
                            <img src="<?= htmlspecialchars($user['photo']) ?>" alt="Current" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0;">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: var(--text-light); margin: 0 auto;"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="photo" accept="image/*" class="form-control" style="font-size: 0.85rem;">
                    <small style="color: var(--text-light);">JPG, PNG, WebP or GIF (max 2MB)</small>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: #f1f5f9; cursor: not-allowed;">
                    <small style="color: var(--text-light);">Email cannot be changed</small>
                </div>
                <div class="form-group">
                    <label>New Password (Optional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Logout -->
    <a href="/logout.php" style="display: block; text-align: center; padding: 1rem; color: #ef4444; font-weight: 600; font-size: 0.95rem; margin-top: 0.5rem; text-decoration: none;">
        🚪 Logout
    </a>

</div>

<script>
// Toggle expandable sections
function toggleSection(id) {
    const section = document.getElementById(id);
    const isActive = section.classList.contains('active');
    
    // Close all sections
    document.querySelectorAll('.expandable-content').forEach(el => {
        el.classList.remove('active');
    });

    // Toggle clicked section
    if (!isActive) {
        section.classList.add('active');
    }
}

// Copy referral code
function copyReferral() {
    const code = document.getElementById('refCode').textContent;
    const text = `🚗 Join RentRide! Use my referral code ${code} to get ₹200 off your first ride! https://rentride.in`;
    
    navigator.clipboard.writeText(text).then(() => {
        document.getElementById('copyMsg').style.display = 'block';
        setTimeout(() => document.getElementById('copyMsg').style.display = 'none', 3000);
    }).catch(() => {
        // Fallback
        prompt('Copy this code:', text);
    });
}

// Remove favourite
function removeFavourite(vehicleId) {
    fetch('/toggle_favourite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'vehicle_id=' + vehicleId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById('fav-' + vehicleId);
            if (item) {
                item.style.transition = 'opacity 0.3s, transform 0.3s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(20px)';
                setTimeout(() => item.remove(), 300);
            }
        }
    });
}

// Rate the app
function rateApp(rating) {
    const stars = document.querySelectorAll('#starRating .star');
    stars.forEach((star, i) => {
        star.classList.toggle('active', i < rating);
    });

    const fd = new FormData();
    fd.append('action', 'rate_app');
    fd.append('rating', rating);

    fetch('/api/profile_actions.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        const messages = {
            1: "😔 We're sorry! We'll improve.",
            2: "😕 Thanks for your feedback!",
            3: "😊 Thanks! We're working to be better.",
            4: "😄 Great! Glad you enjoy RentRide!",
            5: "🎉 Awesome! You love RentRide! ❤️"
        };
        const msgEl = document.getElementById('rateMsg');
        msgEl.textContent = data.success ? messages[rating] : (data.message || 'Error saving rating');
        msgEl.style.display = 'block';
    });
}

// Apply Promo/Referral
function applyPromo() {
    const code = document.getElementById('promoInput').value;
    if (!code) return;

    const fd = new FormData();
    fd.append('action', 'apply_promo');
    fd.append('code', code);

    const msgEl = document.getElementById('promoMsg');
    msgEl.style.display = 'block';
    msgEl.style.color = 'var(--text)';
    msgEl.textContent = 'Processing...';

    fetch('/api/profile_actions.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        msgEl.textContent = data.message;
        msgEl.style.color = data.success ? 'var(--success)' : '#ef4444';
        if (data.success && data.new_balance !== undefined) {
            // Update wallet balance in UI
            document.querySelectorAll('.stat-tile.wallet .stat-value').forEach(el => {
                el.textContent = '₹' + Number(data.new_balance).toLocaleString();
            });
            document.getElementById('promoInput').value = '';
        }
    });
}

// Wallet Top-up
function topUp(amount) {
    if (!confirm('Simulate adding ₹' + amount + ' to your wallet?')) return;

    const fd = new FormData();
    fd.append('action', 'apply_promo');
    fd.append('code', 'TOPUP_' + amount); // I'll handle this in backend or just use a dummy code

    fetch('/api/profile_actions.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.stat-tile.wallet .stat-value').forEach(el => {
                el.textContent = '₹' + Number(data.new_balance).toLocaleString();
            });
            alert('₹' + amount + ' successfully added to your wallet! (Simulation)');
        }
    });
}
</script>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
