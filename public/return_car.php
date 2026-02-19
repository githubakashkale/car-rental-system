<?php
// return_car.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingId = $_GET['booking_id'] ?? 0;
$booking = $db->getBookingById($bookingId);

// Security check: owner only and status confirmed
if (!$booking || $booking['user_id'] != $_SESSION['user_id'] || $booking['booking_status'] !== 'confirmed') {
    header("Location: /my-bookings.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $odometer = $_POST['odometer'] ?? '';
    $fuel = $_POST['fuel_level'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Handle Image Upload
    $imagePath = '';
    if (isset($_FILES['car_photo']) && $_FILES['car_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/assets/uploads/returns/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['car_photo']['name'], PATHINFO_EXTENSION);
        $fileName = "return_" . $bookingId . "_" . time() . "." . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['car_photo']['tmp_name'], $targetPath)) {
            $imagePath = '/assets/uploads/returns/' . $fileName;
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "Car photo is mandatory for return verification.";
    }

    if (!$error && $odometer && $fuel && $condition) {
        $returnData = [
            'odometer' => $odometer,
            'fuel_level' => $fuel,
            'condition' => $condition,
            'image_path' => $imagePath,
            'notes' => $notes
        ];
        
        if ($db->requestReturn($bookingId, $returnData)) {
            $_SESSION['flash_success'] = "Return request submitted! Please wait for admin to verify.";
            header("Location: /my-bookings.php");
            exit;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else if (!$error) {
        $error = "Please fill in all required fields.";
    }
}

require __DIR__ . '/../apps/templates/header.php';
?>

<div class="container" style="margin-top: 2rem; max-width: 600px;">
    <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow);">
        <h1 style="margin-bottom: 0.5rem;">Return Your Ride</h1>
        <p style="color: var(--secondary); margin-bottom: 2rem;">Please provide the current status of <strong><?= htmlspecialchars($booking['vehicle_name']) ?></strong>.</p>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                ⚠️ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Current Odometer Reading (km) *</label>
                <input type="number" name="odometer" class="form-control" required placeholder="e.g. 12450">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fuel Level *</label>
                <div style="display: flex; gap: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="fuel_level" value="Full" required> Full
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="fuel_level" value="Half"> Half
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="fuel_level" value="Low"> Low
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Car Condition *</label>
                <div style="display: grid; gap: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="radio" name="condition" value="Good" required> 
                        <div>
                            <div style="font-weight: 600;">Good</div>
                            <div style="font-size: 0.75rem; color: var(--secondary);">No new scratches or issues</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="radio" name="condition" value="Minor Damage"> 
                        <div>
                            <div style="font-weight: 600;">Minor Damage</div>
                            <div style="font-size: 0.75rem; color: var(--secondary);">Small scratches, dents, or internal stains</div>
                        </div>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <input type="radio" name="condition" value="Major Damage"> 
                        <div>
                            <div style="font-weight: 600; color: #dc2626;">Major Damage</div>
                            <div style="font-size: 0.75rem; color: var(--secondary);">Accident, major dents, or mechanical issues</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Upload Car Photo (Mandatory) *</label>
                <input type="file" name="car_photo" class="form-control" accept="image/*" required style="padding: 0.5rem;">
                <small style="color: var(--secondary);">Please upload a clear photo showing the overall condition of the car.</small>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Additional Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Any specific issues or comments..."></textarea>
            </div>

            <div style="display: flex; gap: 1rem;">
                <a href="/my-bookings.php" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="flex: 2; background: #6366f1;">Submit Return Request</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
