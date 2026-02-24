<?php
// admin/edit_vehicle.php
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

$vehicle = $db->getVehicle($id);
if (!$vehicle) {
    $_SESSION['flash_error'] = "Vehicle not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Serviceable cities for the location dropdown
$serviceableCities = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Hyderabad', 'Pune', 'Jaipur', 'Kolkata', 'Ahmedabad'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vehicle_name' => $_POST['vehicle_name'],
        'make' => $_POST['make'],
        'model' => $_POST['model'],
        'vehicle_type' => $_POST['vehicle_type'],
        'year' => $_POST['year'],
        'price_per_day' => (int)$_POST['price_per_day'],
        'description' => $_POST['description'],
        'image_url' => $_POST['image_url'],
        'location' => $_POST['location'],
        'available' => isset($_POST['available'])
    ];

    if ($db->updateVehicle($id, $data)) {
        $_SESSION['flash_success'] = "Vehicle updated successfully.";
        header("Location: /admin/dashboard.php");
        exit;
    } else {
        $error = "Failed to update vehicle.";
    }
}
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>🚗 Edit Vehicle: <?= htmlspecialchars($vehicle['vehicle_name']) ?></h1>
        <a href="/admin/dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow); max-width: 800px; margin: 0 auto;">
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Vehicle Name (Display)</label>
                    <input type="text" name="vehicle_name" class="form-control" value="<?= htmlspecialchars($vehicle['vehicle_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="vehicle_type" class="form-control" required>
                        <option value="Sedan" <?= $vehicle['vehicle_type'] === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                        <option value="SUV" <?= $vehicle['vehicle_type'] === 'SUV' ? 'selected' : '' ?>>SUV</option>
                        <option value="Hatchback" <?= $vehicle['vehicle_type'] === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                        <option value="Electric" <?= $vehicle['vehicle_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                        <option value="Luxury" <?= $vehicle['vehicle_type'] === 'Luxury' ? 'selected' : '' ?>>Luxury</option>
                        <option value="Coupe" <?= $vehicle['vehicle_type'] === 'Coupe' ? 'selected' : '' ?>>Coupe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Make</label>
                    <input type="text" name="make" class="form-control" value="<?= htmlspecialchars($vehicle['make']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="<?= $vehicle['year'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Price Per Day (₹)</label>
                    <input type="number" name="price_per_day" class="form-control" value="<?= $vehicle['price_per_day'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Location / City</label>
                    <select name="location" class="form-control" required>
                        <?php foreach($serviceableCities as $city): ?>
                            <option value="<?= $city ?>" <?= ($vehicle['location'] ?? 'Mumbai') === $city ? 'selected' : '' ?>><?= $city ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image_url" class="form-control" value="<?= htmlspecialchars($vehicle['image_url']) ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($vehicle['description']) ?></textarea>
            </div>

            <div class="form-group" style="margin-top: 1rem; border-top: 1px solid #edf2f7; padding-top: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="available" id="available" <?= ($vehicle['available'] ?? true) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                <label for="available" style="margin-bottom: 0;">Mark as Published / Available for Booking</label>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <a href="/admin/dashboard.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
