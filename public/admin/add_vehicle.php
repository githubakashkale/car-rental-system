<?php
// admin/add_vehicle.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db->addVehicle(
        $_POST['make'],
        $_POST['model'],
        $_POST['type'], // Added type
        $_POST['year'],
        $_POST['price'],
        $_POST['description'],
        $_POST['image_url'],
        $_POST['location'] // Added location
    );
    
    $_SESSION['flash_success'] = "Vehicle added successfully!";
    header("Location: /admin/dashboard.php");
    exit;
}
?>

<?php require __DIR__ . '/../../apps/templates/header.php'; ?>

<div class="form-card" style="max-width: 600px;">
    <h2>Add New Vehicle</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Make</label>
                <input type="text" name="make" class="form-control" required placeholder="e.g. Toyota">
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" class="form-control" required placeholder="e.g. Camry">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Vehicle Type</label>
                <select name="type" class="form-control" required>
                    <option value="Sedan">Sedan</option>
                    <option value="SUV">SUV</option>
                    <option value="Hatchback">Hatchback</option>
                    <option value="Coupe">Coupe</option>
                    <option value="Electric">Electric</option>
                    <option value="Luxury">Luxury</option>
                </select>
            </div>
            <div class="form-group">
                <label>Location</label>
                <select name="location" class="form-control" required>
                    <option value="Mumbai">Mumbai</option>
                    <option value="Delhi">Delhi</option>
                    <option value="Bangalore">Bangalore</option>
                    <option value="Chennai">Chennai</option>
                    <option value="Hyderabad">Hyderabad</option>
                    <option value="Pune">Pune</option>
                    <option value="Jaipur">Jaipur</option>
                    <option value="Kolkata">Kolkata</option>
                    <option value="Ahmedabad">Ahmedabad</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Year</label>
                <input type="number" name="year" class="form-control" required value="2024">
            </div>
            <div class="form-group">
                <label>Price Per Day (₹)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label>Image URL</label>
            <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Vehicle</button>
        <a href="/admin/dashboard.php" class="btn btn-outline" style="margin-left: 1rem;">Cancel</a>
    </form>
</div>

<?php require __DIR__ . '/../../apps/templates/footer.php'; ?>
