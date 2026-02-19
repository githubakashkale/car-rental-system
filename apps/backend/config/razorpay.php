<?php
// config/razorpay.php
// Razorpay Test Mode credentials
// Replace with your own keys from https://dashboard.razorpay.com/app/keys

define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_SGqkdCyaFNXSSI');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');

// Currency
define('RAZORPAY_CURRENCY', 'INR');

// Company info for checkout
define('RAZORPAY_COMPANY_NAME', 'RentRide');
define('RAZORPAY_COMPANY_DESCRIPTION', 'Premium Vehicle Rentals');
define('RAZORPAY_LOGO', 'https://ui-avatars.com/api/?name=RR&background=4f46e5&color=fff&size=128&bold=true');
?>
