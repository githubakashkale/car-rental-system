<?php
// add_review.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingId = $_GET['booking_id'] ?? 0;
$booking = $db->getBookingById($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id'] || $booking['booking_status'] !== 'completed') {
    header("Location: /my-bookings.php");
    exit;
}

$existingReview = is_string($booking['review'] ?? '{}') ? json_decode($booking['review'] ?? '{}', true) : ($booking['review'] ?? []);
if (!empty($existingReview) && !empty($existingReview['rating'])) {
    $_SESSION['flash_success'] = "You have already reviewed this booking.";
    header("Location: /my-bookings.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a rating between 1 and 5 stars.";
    } else {
        if ($db->addReview($bookingId, $rating, $comment)) {
            $_SESSION['flash_success'] = "Thank you for your feedback! ⭐";
            header("Location: /my-bookings.php");
            exit;
        } else {
            $error = "Failed to save your review.";
        }
    }
}

require __DIR__ . '/../apps/templates/header.php';
?>

<div class="container" style="margin-top: 2rem; max-width: 500px;">
    <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow); text-align: center;">
        <h1 style="margin-bottom: 0.5rem;">Rate Your Experience</h1>
        <p style="color: var(--secondary); margin-bottom: 2rem;">How was your trip with the <strong><?= htmlspecialchars($booking['vehicle_name']) ?></strong>?</p>

        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                ⚠️ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 2rem; flex-direction: row-reverse;">
                <?php for($i=5; $i>=1; $i--): ?>
                    <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" style="display: none;">
                    <label for="star<?= $i ?>" style="font-size: 2.5rem; color: #cbd5e1; cursor: pointer; transition: color 0.2s;" class="star-label">★</label>
                <?php endfor; ?>
            </div>

            <style>
                .star-label:hover,
                .star-label:hover ~ .star-label,
                input[type="radio"]:checked ~ .star-label {
                    color: #f59e0b !important;
                }
            </style>

            <div class="form-group" style="text-align: left; margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Tell us more (Optional)</label>
                <textarea name="comment" class="form-control" rows="4" placeholder="How was the car? How was the service?"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 600; background: #f59e0b; border: none;">Submit Review</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
