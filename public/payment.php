<?php
// payment.php — Razorpay Checkout Page
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/razorpay.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingId = $_GET['booking_id'] ?? null;
if (!$bookingId) {
    $_SESSION['flash_error'] = "Invalid booking.";
    header("Location: /");
    exit;
}

$booking = $db->getBookingById($bookingId);

if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    $_SESSION['flash_error'] = "Booking not found.";
    header("Location: /");
    exit;
}

if (($booking['payment_status'] ?? '') === 'paid') {
    $_SESSION['flash_success'] = "Payment already completed for this booking.";
    header("Location: /my-bookings.php");
    exit;
}

// Amount in paise (Razorpay expects smallest currency unit)
$totalPayable = $booking['total_price'] + ($booking['security_deposit'] ?? 5000);
$amountInPaise = round($totalPayable * 100);
$vehicleName = $booking['vehicle_name'] ?? 'Vehicle Rental';
$userName = $_SESSION['name'] ?? 'Customer';

// Generate a unique receipt ID
$receiptId = 'rcpt_' . $booking['id'] . '_' . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | RentRide</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .payment-container {
            max-width: 560px;
            margin: 3rem auto;
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .payment-header h2 {
            margin-bottom: 0.25rem;
            font-size: 1.5rem;
        }
        .payment-header p {
            opacity: 0.85;
            font-size: 0.95rem;
        }
        .payment-body {
            padding: 2rem;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }
        .payment-row:last-child {
            border-bottom: none;
        }
        .payment-row .label {
            color: var(--text-light);
        }
        .payment-row .value {
            font-weight: 600;
        }
        .payment-total {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            margin-top: 0.5rem;
            border-top: 2px solid var(--primary);
            font-size: 1.2rem;
            font-weight: 700;
        }
        .payment-total .value {
            color: var(--primary);
        }
        .pay-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
            border: none;
            color: white;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s;
        }
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
        }
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .cancel-link:hover {
            color: var(--danger);
        }
    </style>
</head>
<body style="background: var(--background);">
    <div class="payment-container">
        <div class="payment-header">
            <h2>Complete Your Payment</h2>
            <p>Booking #<?= $booking['id'] ?> — <?= htmlspecialchars($vehicleName) ?></p>
        </div>
        <div class="payment-body">
            <div class="payment-row">
                <span class="label">Vehicle</span>
                <span class="value"><?= htmlspecialchars($vehicleName) ?></span>
            </div>
            <div class="payment-row">
                <span class="label">Rental Period</span>
                <span class="value"><?= date('d M', strtotime($booking['start_date'])) ?> → <?= date('d M Y', strtotime($booking['end_date'])) ?></span>
            </div>
            <div class="payment-row">
                <span class="label">Rental Amount</span>
                <span class="value">₹<?= number_format($booking['total_price'], 2) ?></span>
            </div>
            <div class="payment-row">
                <span class="label">Security Deposit</span>
                <span class="value" style="color: var(--success);">₹<?= number_format($booking['security_deposit'] ?? 5000, 2) ?></span>
            </div>
            <div class="payment-total">
                <span>Total Payable</span>
                <span class="value">₹<?= number_format($totalPayable, 2) ?></span>
            </div>

            <button class="pay-btn" id="payBtn" onclick="startPayment()">
                💳 Pay ₹<?= number_format($totalPayable, 2) ?> Now
            </button>

            <div class="secure-badge">
                🔒 Secured by Razorpay | 256-bit SSL Encryption
            </div>

            <a href="/" class="cancel-link">Cancel and go back</a>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function startPayment() {
            const options = {
                key: '<?= RAZORPAY_KEY_ID ?>',
                amount: <?= $amountInPaise ?>,
                currency: '<?= RAZORPAY_CURRENCY ?>',
                name: '<?= RAZORPAY_COMPANY_NAME ?>',
                description: 'Booking #<?= $booking['id'] ?> — <?= addslashes($vehicleName) ?>',
                image: '<?= RAZORPAY_LOGO ?>',
                handler: function(response) {
                    // Payment success — submit to verify
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/verify_payment.php';

                    const fields = {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id || '',
                        razorpay_signature: response.razorpay_signature || '',
                        booking_id: <?= $booking['id'] ?>
                    };

                    for (const [key, val] of Object.entries(fields)) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = val;
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                },
                prefill: {
                    name: '<?= addslashes($userName) ?>',
                    email: '',
                    contact: ''
                },
                notes: {
                    booking_id: '<?= $booking['id'] ?>',
                    receipt: '<?= $receiptId ?>'
                },
                theme: {
                    color: '#4f46e5'
                },
                modal: {
                    ondismiss: function() {
                        // User closed the payment modal
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function(response) {
                alert('Payment failed: ' + response.error.description);
            });
            rzp.open();
        }
    </script>
</body>
</html>
