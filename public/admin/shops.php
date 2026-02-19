<?php
// admin/shops.php
require_once __DIR__ . '/../../apps/backend/config/shops.php';
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit;
}

$allShops = $SHOPS;
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>🏪 Shop Management</h1>
        <div>
            <button class="btn btn-primary" onclick="alert('Shop addition is coming in the next update!')">+ Add New Shop</button>
            <a href="/admin/dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
        </div>
    </div>

    <?php foreach ($allShops as $city => $shops): ?>
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary); display: inline-block; padding-bottom: 0.25rem;"><?= htmlspecialchars($city) ?></h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <?php foreach ($shops as $s): ?>
                    <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); position: relative;">
                        <span style="position: absolute; top: 1rem; right: 1rem; font-size: 0.7rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: var(--secondary);"><?= $s['id'] ?></span>
                        <h4 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($s['name']) ?></h4>
                        <p style="font-size: 0.85rem; color: var(--secondary); margin-bottom: 0.5rem;">📍 <?= htmlspecialchars($s['address']) ?></p>
                        <p style="font-size: 0.85rem; color: var(--secondary); margin-bottom: 1rem;">📞 <?= htmlspecialchars($s['phone']) ?></p>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline btn-sm" style="flex: 1; font-size: 0.75rem;">Edit</button>
                            <button class="btn btn-danger btn-sm" style="flex: 1; font-size: 0.75rem; background: #fee2e2; color: #991b1b; border: none;">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
