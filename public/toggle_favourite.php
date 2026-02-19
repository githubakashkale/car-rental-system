<?php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$vehicleId = intval($_POST['vehicle_id'] ?? 0);
if (!$vehicleId) {
    echo json_encode(['error' => 'Invalid vehicle']);
    exit;
}

$added = $db->toggleFavourite($_SESSION['user_id'], $vehicleId);
echo json_encode(['success' => true, 'added' => $added]);
