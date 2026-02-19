<?php
// public/debug_auth.php
session_start();
require_once __DIR__ . '/../apps/backend/config/db.php';

echo "<h1>Debug Authentication</h1>";

echo "<h2>1. Current Session</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>2. Database Lookup (by Session ID)</h2>";
if (isset($_SESSION['user_id'])) {
    $user = $db->getUserById($_SESSION['user_id']);
    echo "<pre>" . print_r($user, true) . "</pre>";
} else {
    echo "No user_id in session.";
}

echo "<h2>3. Test Email Lookup</h2>";
echo '<form method="GET">
    <input type="text" name="test_email" placeholder="Enter email to test" value="' . htmlspecialchars($_GET['test_email'] ?? '') . '">
    <button type="submit">Lookup</button>
</form>';

if (isset($_GET['test_email'])) {
    $testEmail = $_GET['test_email'];
    echo "Looking up: " . htmlspecialchars($testEmail) . "<br>";
    echo "Normalized (check logic): " . strtolower(trim($testEmail)) . "<br>";
    
    $u = $db->findUserByEmail($testEmail);
    if ($u) {
        echo "<h3 style='color:green'>Found User:</h3>";
        echo "<pre>" . print_r($u, true) . "</pre>";
    } else {
        echo "<h3 style='color:red'>User Not Found</h3>";
        // Debug raw query
        $pdo = $db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email LIKE ?");
        $stmt->execute(['%' . $testEmail . '%']);
        $similar = $stmt->fetchAll();
        if ($similar) {
            echo "Found similar emails via LIKE:<br>";
            echo "<pre>" . print_r($similar, true) . "</pre>";
        }
    }
}
?>
