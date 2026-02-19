<?php
// config/db.php

class PostgresDB {
    private $pdo;

    public function __construct($dbname = 'rentride', $user = 'akash') {
        // Render environment variables
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_ENV['DB_NAME'] ?? $dbname;
        $username = $_ENV['DB_USER'] ?? $user;
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $port = $_ENV['DB_PORT'] ?? 5432;

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbName";

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    // --- Users ---
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function createUser($name, $email, $password, $role = 'user', $phone = '') {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?) RETURNING id");
        $stmt->execute([$name, $email, $password, $role, $phone]);
        return $stmt->fetchColumn();
    }

    public function updateUser($id, $name, $password = null, $phone = null, $address = null, $city = null, $license = null, $photo = null) {
        $query = "UPDATE users SET name = ?";
        $params = [$name];

        if ($password) {
            $query .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ($phone !== null) {
            $query .= ", phone = ?";
            $params[] = $phone;
        }
        if ($address !== null) {
            $query .= ", address = ?";
            $params[] = $address;
        }
        if ($city !== null) {
            $query .= ", city = ?";
            $params[] = $city;
        }
        if ($license !== null) {
            $query .= ", license_number = ?";
            $params[] = $license;
        }
        if ($photo !== null) {
            $query .= ", photo = ?";
            $params[] = $photo;
        }

        $query .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
    }

    public function toggleUserStatus($id, $status = null) {
        if ($status !== null) {
            $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } else {
            // Toggle between active and inactive status
            $stmt = $this->pdo->prepare("UPDATE users SET role = CASE WHEN role = 'user' THEN 'inactive' ELSE 'user' END WHERE id = ?");
            $stmt->execute([$id]);
        }
        return true;
    }

    // --- Vehicles ---
    public function getAllVehicles() {
        $stmt = $this->pdo->query("SELECT * FROM vehicles");
        return $stmt->fetchAll();
    }

    public function getVehicle($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function addVehicle($make, $model, $type, $year, $price, $desc, $img, $location = 'Mumbai') {
        $stmt = $this->pdo->prepare("INSERT INTO vehicles (vehicle_name, vehicle_type, make, model, year, price_per_day, description, image_url, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$make . ' ' . $model, $type, $make, $model, $year, $price, $desc, $img, $location]);
        $this->logActivity($_SESSION['user_id'] ?? 0, 'Vehicle Added', "$make $model added.");
    }

    public function deleteVehicle($id) {
        $stmt = $this->pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function toggleMaintenance($id) {
        $stmt = $this->pdo->prepare("UPDATE vehicles SET maintenance_status = NOT maintenance_status, availability_status = CASE WHEN NOT maintenance_status THEN 'Maintenance' ELSE 'Available' END WHERE id = ? RETURNING maintenance_status");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();
        $this->logActivity($_SESSION['user_id'] ?? 0, 'Maintenance Toggle', "Vehicle ID: $id -> " . ($status ? 'On' : 'Off'));
    }

    public function updateVehicle($id, $data) {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        $stmt = $this->pdo->prepare("UPDATE vehicles SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    // --- Bookings ---
    public function createBooking($userId, $vehicleId, $start, $end, $total, $deposit = 5000, $deliveryMode = 'pickup', $deliveryAddress = '', $pickupShop = '', $customerPhone = '') {
        if ($this->checkConflict($vehicleId, $start, $end)) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, total_price, security_deposit, delivery_mode, delivery_address, pickup_shop, customer_phone, booking_status, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'payment_pending', 'pending') RETURNING id");
        $stmt->execute([$userId, $vehicleId, $start, $end, $total, $deposit, $deliveryMode, $deliveryAddress, $pickupShop, $customerPhone]);
        $id = $stmt->fetchColumn();

        $this->logActivity($userId, 'Booking Created', "Vehicle ID: $vehicleId, Total: $total, Delivery: $deliveryMode");
        return $id;
    }

    public function getBookingById($id) {
        $stmt = $this->pdo->prepare("SELECT b.*, v.vehicle_name, v.image_url FROM bookings b JOIN vehicles v ON b.vehicle_id = v.id WHERE b.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateBookingPayment($id, $paymentId, $razorpayOrderId) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET booking_status = 'pending', payment_status = 'paid', payment_id = ?, razorpay_order_id = ? WHERE id = ?");
        $stmt->execute([$paymentId, $razorpayOrderId, $id]);
        
        // Log activity (need userId)
        $booking = $this->getBookingById($id);
        $this->logActivity($booking['user_id'] ?? 0, 'Payment Completed', "Booking #$id, Payment: $paymentId");
    }

    public function getBookingsByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT b.*, v.vehicle_name, v.image_url FROM bookings b JOIN vehicles v ON b.vehicle_id = v.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllBookings() {
        $stmt = $this->pdo->query("SELECT b.*, v.vehicle_name, u.name as user_name FROM bookings b JOIN vehicles v ON b.vehicle_id = v.id JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC");
        return $stmt->fetchAll();
    }

    public function updateBookingStatus($id, $status) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE bookings SET booking_status = ? WHERE id = ? RETURNING vehicle_id");
            $stmt->execute([$status, $id]);
            $vehicleId = $stmt->fetchColumn();

            if ($status === 'confirmed') {
                $this->updateVehicleAvailability($vehicleId, 'Rented', false);
            } elseif (in_array($status, ['cancelled', 'completed'])) {
                $this->updateVehicleAvailability($vehicleId, 'Available', true);
            }

            $this->logActivity($_SESSION['user_id'] ?? 0, 'Booking Status Update', "Booking ID: $id -> $status");
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function updateVehicleAvailability($vehicleId, $status, $available) {
        $stmt = $this->pdo->prepare("UPDATE vehicles SET availability_status = ?, available = ? WHERE id = ?");
        $stmt->execute([$status, $available ? 'true' : 'false', $vehicleId]);
    }

    public function cancelBooking($id, $refundAmount = 0, $depositRefund = 0) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET booking_status = 'cancelled', refund_amount = ?, deposit_refund = ?, cancelled_at = NOW() WHERE id = ?");
        $stmt->execute([$refundAmount, $depositRefund, $id]);
    }

    public function requestReturn($bookingId, $data) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET booking_status = 'return_pending', return_request = ? WHERE id = ?");
        $stmt->execute([json_encode($data), $bookingId]);
        return true;
    }

    public function adminSetFines($bookingId, $damageFee, $lateFee, $shouldBlacklist = false) {
        $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $stmt = $this->pdo->prepare("UPDATE bookings SET penalty = ?, late_fee = ?, should_blacklist = ?, approval_otp = ?, late_hours = GREATEST(0, CEIL(EXTRACT(EPOCH FROM (NOW() - end_date)) / 3600)) WHERE id = ?");
        $stmt->execute([$damageFee, $lateFee, $shouldBlacklist ? 'true' : 'false', $otp, $bookingId]);
        
        return $otp;
    }

    public function finalizeReturn($bookingId, $otp) {
        $booking = $this->getBookingById($bookingId);
        if (!$booking) return ['success' => false, 'error' => 'Booking not found'];
        if (($booking['approval_otp'] ?? '') !== $otp) return ['success' => false, 'error' => 'Invalid OTP'];

        $this->pdo->beginTransaction();
        try {
            $penalty = $booking['penalty'] ?? 0;
            $lateFee = $booking['late_fee'] ?? 0;
            $deposit = $booking['security_deposit'] ?? 5000;
            $finalRefund = $deposit - $penalty - $lateFee;

            $stmt = $this->pdo->prepare("UPDATE bookings SET booking_status = 'completed', final_refund = ?, completed_at = NOW() WHERE id = ?");
            $stmt->execute([$finalRefund, $bookingId]);

            // Update Vehicle Damage History
            $stmt = $this->pdo->prepare("UPDATE vehicles SET damage_history = damage_history || ?, availability_status = 'Available', available = TRUE WHERE id = ?");
            $damageEntry = json_encode([
                'date' => date('Y-m-d'),
                'booking_id' => $bookingId,
                'condition' => json_decode($booking['return_request'], true)['condition'] ?? 'Unknown',
                'penalty' => $penalty,
                'notes' => json_decode($booking['return_request'], true)['notes'] ?? ''
            ]);
            $stmt->execute(['[' . $damageEntry . ']', $booking['vehicle_id']]);

            if ($booking['should_blacklist']) {
                $this->toggleUserStatus($booking['user_id'], 'blacklisted');
            }

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addReview($bookingId, $rating, $comment) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET review = ? WHERE id = ?");
        return $stmt->execute([json_encode(['rating' => (int)$rating, 'comment' => $comment, 'date' => date('Y-m-d H:i:s')]), $bookingId]);
    }

    public function checkConflict($vehicleId, $start, $end) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vehicle_id = ? AND booking_status != 'cancelled' AND (
            (start_date BETWEEN ? AND ?) OR
            (end_date BETWEEN ? AND ?) OR
            (? BETWEEN start_date AND end_date)
        )");
        $stmt->execute([$vehicleId, $start, $end, $start, $end, $start]);
        return $stmt->fetchColumn() > 0;
    }

    public function calculateDynamicPrice($vehicleId, $start, $end) {
        $vehicle = $this->getVehicle($vehicleId);
        if (!$vehicle) return 0;

        $basePrice = $vehicle['price_per_day'];
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        
        $totalPrice = 0;
        $currentTs = $startTs;

        while ($currentTs < $endTs) {
            $dayOfWeek = date('N', $currentTs);
            $dailyRate = $basePrice;
            if ($dayOfWeek >= 6) $dailyRate *= 1.10; 
            $totalPrice += $dailyRate;
            $currentTs = strtotime('+1 day', $currentTs);
        }

        $days = ($endTs - $startTs) / (60 * 60 * 24);
        if ($days > 7) $totalPrice *= 0.85; 

        return round($totalPrice, 2);
    }

    public function calculateTotalWithDriver($basePrice, $withDriver, $days) {
        $driverCost = $withDriver ? (500 * $days) : 0;
        return $basePrice + $driverCost;
    }

    public function addRewardPoints($userId, $points) {
        $stmt = $this->pdo->prepare("UPDATE users SET points = COALESCE(points, 0) + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
    }

    public function getUserPoints($userId) {
        $stmt = $this->pdo->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function logActivity($userId, $action, $details) {
        $stmt = $this->pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $details]);
    }

    public function getActivityLogs() {
        $stmt = $this->pdo->query("SELECT * FROM activity_logs ORDER BY timestamp DESC");
        return $stmt->fetchAll();
    }
    
    public function getStats() {
        $stmt = $this->pdo->query("SELECT 
            (SELECT COUNT(*) FROM vehicles) as vehicles,
            (SELECT COUNT(*) FROM bookings) as bookings,
            (SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE booking_status != 'cancelled') as revenue");
        return $stmt->fetch();
    }

    public function getWalletBalance($userId) {
        $stmt = $this->pdo->prepare("SELECT wallet FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function addToWallet($userId, $amount) {
        $stmt = $this->pdo->prepare("UPDATE users SET wallet = COALESCE(wallet, 0) + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
    }

    public function toggleFavourite($userId, $vehicleId) {
        $user = $this->getUserById($userId);
        $favs = json_decode($user['favourites'] ?? '[]', true);
        $key = array_search($vehicleId, $favs);
        
        if ($key !== false) {
            array_splice($favs, $key, 1);
            $res = false;
        } else {
            $favs[] = $vehicleId;
            $res = true;
        }

        $stmt = $this->pdo->prepare("UPDATE users SET favourites = ? WHERE id = ?");
        $stmt->execute([json_encode($favs), $userId]);
        return $res;
    }

    public function getFavourites($userId) {
        $user = $this->getUserById($userId);
        $favIds = json_decode($user['favourites'] ?? '[]', true);
        if (empty($favIds)) return [];

        $in = str_repeat('?,', count($favIds) - 1) . '?';
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE id IN ($in)");
        $stmt->execute($favIds);
        return $stmt->fetchAll();
    }

    public function saveChatLog($userId, $userMsg, $botReply) {
        $stmt = $this->pdo->prepare("INSERT INTO chat_history (id, user_id, user_message, bot_reply) VALUES (?, ?, ?, ?)");
        $stmt->execute([uniqid(), $userId, $userMsg, $botReply]);
    }

    public function getChatHistory($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE user_id = ? ORDER BY timestamp DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findCarsByBudget($maxPrice) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE available = TRUE AND price_per_day <= ?");
        $stmt->execute([$maxPrice]);
        return $stmt->fetchAll();
    }

    public function findCarsByType($type) {
        $stmt = $this->pdo->prepare("SELECT * FROM vehicles WHERE available = TRUE AND LOWER(vehicle_type) = LOWER(?)");
        $stmt->execute([$type]);
        return $stmt->fetchAll();
    }

    public function addAppRating($userId, $rating) {
        $stmt = $this->pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'App Rating', ?)");
        $stmt->execute([$userId, "User rated the app: $rating stars"]);
        return true;
    }

    public function applyPromoCode($userId, $code) {
        $code = strtoupper(trim($code));
        $reward = 0;
        $msg = "";

        if ($code === 'FIRST20') {
            $reward = 100; // Sample reward
            $msg = "Welcome offer applied! ₹100 added to wallet.";
        } elseif (strpos($code, 'RENT') === 0 && strlen($code) === 10) {
            $reward = 200;
            $msg = "Referral code applied! ₹200 added to wallet.";
        } elseif (strpos($code, 'TOPUP_') === 0) {
            $reward = (int)str_replace('TOPUP_', '', $code);
            $msg = "Wallet topped up with ₹$reward.";
        } else {
            return ['success' => false, 'message' => 'Invalid or expired code.'];
        }

        $this->addToWallet($userId, $reward);
        $this->logActivity($userId, 'Promo Applied', "Code: $code, Reward: ₹$reward");
        return ['success' => true, 'message' => $msg, 'new_balance' => $this->getWalletBalance($userId)];
    }
}

// For compatibility during migration/testing, we'll initialize with 'rentride' defaults
$db = new PostgresDB('rentride', 'akash');
?>
