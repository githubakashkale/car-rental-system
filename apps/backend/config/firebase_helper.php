<?php
// apps/backend/config/firebase_helper.php

require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseHelper {
    private static $instance = null;
    private $auth;

    private function __construct() {
        $serviceAccountPath = __DIR__ . '/key/firebase_admin_sdk.json';
        
        if (!file_exists($serviceAccountPath)) {
            throw new Exception("Firebase Service Account Key not found at: " . $serviceAccountPath);
        }

        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->auth = $factory->createAuth();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new FirebaseHelper();
        }
        return self::$instance;
    }

    /**
     * Verifies a Firebase ID token sent from the client.
     * @param string $idTokenString
     * @return \Kreait\Firebase\Auth\Token\VerifiedIdToken|null
     */
    public function verifyIdToken($idTokenString) {
        try {
            return $this->auth->verifyIdToken($idTokenString);
        } catch (FailedToVerifyToken $e) {
            error_log('Firebase Token Verification Failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('Firebase Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Gets user data from Firebase by UID.
     * @param string $uid
     * @return \Kreait\Firebase\Auth\UserRecord
     */
    public function getUser($uid) {
        return $this->auth->getUser($uid);
    }
}
?>
