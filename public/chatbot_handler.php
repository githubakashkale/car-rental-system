<?php
// apps/frontend/chatbot_handler.php
session_start();
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/huggingface.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

if (!$message) {
    echo json_encode(['reply' => "I didn't quite catch that. How can I help?"]);
    exit;
}

// 1. Prepare Car Data Context for AI
$allCars = $db->data['vehicles'] ?? [];
$carContext = "Current Fleet Available for Rent:\n";
foreach ($allCars as $car) {
    if ($car['available']) {
        $carContext .= "- {$car['vehicle_name']} ({$car['vehicle_type']}): ₹{$car['price_per_day']}/day. Transmission: {$car['transmission']}. Seats: {$car['seating_capacity']}.\n";
    }
}

// 2. Call Hugging Face API (Mistral-7B)
$reply = "";
$apiUrl = "https://router.huggingface.co/hf-inference/models/" . HF_MODEL;

// Construct Prompt for Mistral (Instruction format)
$prompt = "<s>[INST] You are a helpful and professional car rental assistant for RentRide. 
You have access to the following live car data:
$carContext

Guidelines:
- Be polite and concise.
- Suggest cars from the context if asked for budget or type.
- Help guide them to book on the website.
- Keep responses simple for a chat interface.

User Message: $message [/INST]";

$payload = json_encode([
    'inputs' => $prompt,
    'parameters' => [
        'max_new_tokens' => 250,
        'temperature' => 0.7,
        'return_full_text' => false
    ]
]);

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n" .
                     "Authorization: Bearer " . HF_API_KEY . "\r\n",
        'method'  => 'POST',
        'content' => $payload,
        'ignore_errors' => true
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

if ($response !== false) {
    $result = json_decode($response, true);
    // Hugging Face returns an array of objects for text generation
    if (isset($result[0]['generated_text'])) {
        $reply = trim($result[0]['generated_text']);
    } elseif (isset($result['error'])) {
        // Log error internally if needed, fallback will trigger
    }
}

// 3. Fallback Logic (if AI fails or no reply)
if (empty($reply)) {
    $msg = strtolower($message);
    if (preg_match('/(?:under|below|less than|within)\s*(?:rs\.?|₹|inr)?\s*(\d+)/', $msg, $matches)) {
        $budget = (int)$matches[1];
        $cars = $db->findCarsByBudget($budget);
        if (!empty($cars)) {
            $reply = "I found these cars under ₹" . number_format($budget) . " per day:<br><br>";
            foreach (array_slice($cars, 0, 3) as $car) {
                $reply .= "🚗 <strong>{$car['vehicle_name']}</strong> - ₹{$car['price_per_day']}/day<br>";
            }
        } else {
            $reply = "Sorry, I couldn't find any cars under ₹" . number_format($budget) . ".";
        }
    } elseif (preg_match('/(suv|sedan|hatchback|luxury)/', $msg, $matches)) {
        $type = $matches[1];
        $cars = $db->findCarsByType($type);
        if (!empty($cars)) {
            $reply = "Here are some " . strtoupper($type) . "s available:<br><br>";
            foreach (array_slice($cars, 0, 3) as $car) {
                $reply .= "✨ <strong>{$car['vehicle_name']}</strong> - ₹{$car['price_per_day']}/day<br>";
            }
        }
    } elseif (msg_contains($msg, ['refund', 'cancel', 'return money'])) {
        $reply = "<strong>Refund Policy:</strong><br>• 100% refund if cancelled 48+ hours before pickup.<br>• 50% refund within 24-48 hours.<br>• No refund if less than 24 hours remains.<br><br>Security deposits are fully refunded after a clean vehicle return!";
    } elseif (msg_contains($msg, ['delivery', 'doorstep', 'pickup', 'home'])) {
        $reply = "We offer <strong>Home Delivery</strong> for a flat fee of ₹500! Our driver will bring the car to your location and collect it later. Alternatively, you can pickup from any of our city hubs for free.";
    } elseif (msg_contains($msg, ['contact', 'support', 'phone', 'call', 'email', 'help'])) {
        $reply = "You can reach our 24/7 Priority Support at:<br>📞 <strong>+91 98765 43210</strong><br>✉️ <strong>cars.rentride@gmail.com</strong>";
    } elseif (msg_contains($msg, ['document', 'license', 'age', 'id proof'])) {
        $reply = "To rent a car, you need:<br>1. A valid <strong>Driving License</strong>.<br>2. To be aged <strong>21 or above</strong>.<br>3. A government-issued <strong>ID proof</strong> (Aadhar/Passport).";
    } elseif (msg_contains($msg, ['thank', 'thanks', 'ok', 'good', 'bye'])) {
        $reply = "You're very welcome! 😊 I'm always here if you need anything else. Have a great day with RentRide!";
    } else {
        $reply = "Hello! I'm your RentRide assistant. I can help you with car searches (e.g., 'SUV under 3000'), or answer questions about 'refunds', 'delivery', 'documents', and 'support'. How can I help?";
    }
}

// Clean up formatting for chat UI
$reply = str_replace(["**", "\n\n", "\n"], ["", "<br>", "<br>"], $reply);

// Save log
$db->saveChatLog($userId, $message, $reply);

echo json_encode(['reply' => $reply]);

// Helper for keyword searching
function msg_contains($message, $keywords) {
    foreach ($keywords as $kw) {
        if (strpos($message, $kw) !== false) return true;
    }
    return false;
}
?>
