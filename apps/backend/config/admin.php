<?php
// apps/backend/config/admin.php
// Central admin configuration — ONLY this email has admin access.

define('ADMIN_EMAIL', 'cars.rentride@gmail.com');

/**
 * Check if the given email is the designated admin.
 * @param string $email
 * @return bool
 */
function isAdminEmail($email) {
    return strtolower(trim($email)) === strtolower(ADMIN_EMAIL);
}
