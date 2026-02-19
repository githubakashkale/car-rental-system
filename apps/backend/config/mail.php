<?php
// apps/backend/config/mail.php

// SMTP Configuration
define('MAIL_HOST', 'smtp.gmail.com'); // Example: smtp.gmail.com
define('MAIL_PORT', 587);              // Example: 587 for TLS, 465 for SSL
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: 'cars.rentride@gmail.com'); // Your email
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');   // Your app password
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'cars.rentride@gmail.com');
define('MAIL_FROM_NAME', 'RentRide Confirmation');
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
?>
