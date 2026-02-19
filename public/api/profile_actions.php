<?php
// api/profile_actions.php
require_once __DIR__ . '/../../backend/config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'rate_app':
        $rating = (int)($_POST['rating'] ?? 0);
        if ($rating >= 1 && $rating <= 5) {
            $db->addAppRating($_SESSION['user_id'], $rating);
            $response = ['success' => true, 'message' => 'Rating saved. Thank you!'];
        }
        break;

    case 'apply_promo':
        $code = $_POST['code'] ?? '';
        if ($code) {
            $response = $db->applyPromoCode($_SESSION['user_id'], $code);
        }
        break;
}

echo json_encode($response);
