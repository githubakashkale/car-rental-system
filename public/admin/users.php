<?php
// admin/users.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

// Handle Status Toggle
if (isset($_POST['toggle_status'])) {
    $db->toggleUserStatus($_POST['user_id']);
    $_SESSION['flash_success'] = "User status updated.";
    header("Location: /admin/users.php");
    exit;
}

$users = $db->getAllUsers();
$bookings = $db->getAllBookings();

// Calculate user stats
foreach ($users as &$u) {
    $u['booking_count'] = 0;
    $u['total_spent'] = 0;
    foreach ($bookings as $b) {
        if ($b['user_id'] == $u['id']) {
            $u['booking_count']++;
            if ($b['booking_status'] !== 'cancelled') {
                $u['total_spent'] += $b['total_price'];
            }
        }
    }
}
unset($u);

// Search filter
$search = $_GET['search'] ?? '';
if ($search) {
    $users = array_filter($users, function($u) use ($search) {
        return stripos($u['name'], $search) !== false || stripos($u['email'], $search) !== false;
    });
}
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>User Management</h1>
        <a href="/admin/dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
    </div>

    <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 2rem;">
        <form method="GET" style="display: flex; gap: 1rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if($search): ?>
                <a href="/admin/users.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Join Date</th>
                    <th>Bookings</th>
                    <th>Total Spent</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                        <div style="font-size: 0.8rem; color: var(--secondary);"><?= htmlspecialchars($u['email']) ?></div>
                    </td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td><?= $u['booking_count'] ?></td>
                    <td>₹<?= number_format($u['total_spent'], 2) ?></td>
                    <td>
                        <?php 
                            $userRole = $u['role'] ?? 'user';
                            $isActive = in_array($userRole, ['user', 'admin']);
                            $statusLabel = $userRole === 'admin' ? 'ADMIN' : ($userRole === 'blacklisted' ? 'BLACKLISTED' : ($isActive ? 'ACTIVE' : 'INACTIVE'));
                            $statusBg = $isActive ? '#dcfce7' : '#fee2e2';
                            $statusColor = $isActive ? '#166534' : '#991b1b';
                            if ($userRole === 'admin') { $statusBg = '#e0e7ff'; $statusColor = '#4338ca'; }
                        ?>
                        <span style="padding: 0.25rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600;
                            background: <?= $statusBg ?>;
                            color: <?= $statusColor ?>;">
                            <?= $statusLabel ?>
                        </span>
                    </td>
                    <td>
                        <?php if($u['role'] !== 'admin'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm" style="font-size: 0.75rem; 
                                    background: <?= $isActive ? '#ef4444' : '#10b981' ?>; color: white;">
                                    <?= $isActive ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <small style="color: var(--secondary); italic;">System Admin</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
