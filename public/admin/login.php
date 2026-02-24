<?php
// admin/login.php
require_once __DIR__ . '/../../apps/backend/config/db.php';
require_once __DIR__ . '/../../apps/backend/config/admin.php';
require_once __DIR__ . '/../../apps/backend/config/firebase_helper.php';

session_start();

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: /admin/dashboard.php");
    exit;
}

// Handle AJAX Login via ID Token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idToken'])) {
    header('Content-Type: application/json');
    $idToken = $_POST['idToken'];

    try {
        $firebase = FirebaseHelper::getInstance();
        $verifiedToken = $firebase->verifyIdToken($idToken);

        if (!$verifiedToken) {
            echo json_encode(['success' => false, 'error' => 'Invalid or expired token.']);
            exit;
        }

        $claims = $verifiedToken->claims();
        $email = $claims->get('email');
        
        // ENFORCE: Only the designated admin email can access
        if (!isAdminEmail($email)) {
            echo json_encode(['success' => false, 'error' => 'Access denied. Only the designated administrator (' . ADMIN_EMAIL . ') can access this panel.']);
            exit;
        }

        // Find user by email in our DB
        $user = $db->findUserByEmail($email);

        if ($user) {
            // Ensure DB role is set to admin
            if ($user['role'] !== 'admin') {
                $pdo = $db->getPdo();
                $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                $stmt->execute([$user['id']]);
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = 'admin';
            
            echo json_encode(['success' => true, 'redirect' => '/admin/dashboard.php']);
        } else {
            // Auto-create admin account if doesn't exist yet
            $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $userId = $db->createUser('RentRide Admin', $email, $randomPassword, 'admin');
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['name'] = 'RentRide Admin';
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'admin';
            
            echo json_encode(['success' => true, 'redirect' => '/admin/dashboard.php']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Authentication error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | RentRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --bg: #f8fafc;
            --white: #ffffff;
            --error: #ef4444;
            --success: #10b981;
            --radius: 0.75rem;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); height: 100vh; display: flex; align-items: center; justify-content: center; color: #1e293b; }

        .login-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header { text-align: center; margin-bottom: 2rem; }
        .logo { font-size: 1.75rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .logo span { color: #1e293b; }
        .header p { color: var(--secondary); font-size: 0.95rem; }

        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

        .auth-msg {
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: none;
        }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }

        .footer-links { text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--secondary); }
        .footer-links a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <div class="logo">Rent<span>Ride</span> <small style="font-size: 0.8rem; background: #e0e7ff; padding: 2px 8px; border-radius: 4px; margin-left: 5px;">ADMIN</small></div>
            <p>Professional Dashboard Access</p>
        </div>

        <div id="loginMsg" class="auth-msg"></div>

        <!-- Google Login for Admins -->
        <button onclick="handleGoogleAdminLogin()" class="btn" style="background: white; color: #1e293b; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; width: 100%;">
            <svg style="width: 18px; height: 18px; margin-right: 8px;" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Sign in with Google
        </button>

        <div style="text-align: center; margin-bottom: 1.5rem; color: var(--secondary); font-size: 0.8rem; position: relative;">
            <span style="background: white; padding: 0 10px; position: relative; z-index: 1;">or email login</span>
            <div style="position: absolute; top: 50%; left: 0; right: 0; border-top: 1px solid #e2e8f0;"></div>
        </div>

        <form id="adminLoginForm" onsubmit="handleAdminLogin(event)">
            <div class="form-group">
                <label>Administrator Email</label>
                <input type="email" id="adminEmail" class="form-control" required placeholder="admin@rentride.com" autofocus>
            </div>
            <div class="form-group">
                <label>Master Password</label>
                <input type="password" id="adminPassword" class="form-control" required placeholder="••••••••">
            </div>

            <button type="submit" id="loginBtn" class="btn btn-primary">
                Secure Login
            </button>
        </form>

    </div>

    <!-- Firebase SDK (Compatibility Mode) -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
    <script src="/assets/js/firebase-config.js"></script>

    <script>
        const msgEl = document.getElementById('loginMsg');
        const btn = document.getElementById('loginBtn');

        function showMsg(text, type) {
            msgEl.textContent = text;
            msgEl.className = 'auth-msg ' + type;
            msgEl.style.display = 'block';
        }

        async function handleGoogleAdminLogin() {
            msgEl.style.display = 'none';
            const provider = new firebase.auth.GoogleAuthProvider();
            
            try {
                const result = await firebase.auth().signInWithPopup(provider);
                const user = result.user;
                const idToken = await user.getIdToken();

                btn.disabled = true;
                btn.textContent = 'Verifying Admin...';

                const response = await fetch('/admin/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `idToken=${encodeURIComponent(idToken)}`
                });

                const data = await response.json();

                if (data.success) {
                    showMsg('Admin Verified! Redirecting...', 'success');
                    setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    showMsg(data.error || 'Access Denied.', 'error');
                    await firebase.auth().signOut();
                    btn.disabled = false;
                    btn.textContent = 'Secure Login';
                }
            } catch (error) {
                console.error("Firebase Admin Login Error Details:", error);
                let msg = error.message;
                if (error.code === 'auth/internal-error') {
                    msg = "Firebase Internal Error. Check browser console for details (likely API restriction or unauthorized domain).";
                }
                showMsg(msg, 'error');
            }
        }

        async function handleAdminLogin(e) {
            e.preventDefault();
            const email = document.getElementById('adminEmail').value.trim();
            const password = document.getElementById('adminPassword').value;

            if (!email || !password) {
                showMsg('Please enter both email and password.', 'error');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Verifying Credentials...';
            msgEl.style.display = 'none';

            try {
                // 1. Sign in with Firebase client-side
                const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);
                const user = userCredential.user;

                // 2. Get the ID Token
                const idToken = await user.getIdToken();

                // 3. Send ID Token to our server for professional verification via Admin SDK
                const response = await fetch('/admin/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `idToken=${encodeURIComponent(idToken)}`
                });

                const data = await response.json();

                if (data.success) {
                    showMsg('Access Granted! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showMsg(data.error || 'Access Denied.', 'error');
                    await firebase.auth().signOut();
                    btn.disabled = false;
                    btn.textContent = 'Secure Login';
                }

            } catch (error) {
                console.error("Firebase Admin Login Error Details:", error);
                let errorMsg = 'Authentication failed.';
                if (error.code === 'auth/user-not-found' || error.code === 'auth/wrong-password') {
                    errorMsg = 'Invalid admin credentials.';
                } else if (error.code === 'auth/too-many-requests') {
                    errorMsg = 'Too many failed attempts. Please try again later.';
                } else if (error.code === 'auth/internal-error') {
                    errorMsg = "Firebase Internal Error. Check browser console for details.";
                } else {
                    errorMsg = error.message;
                }
                showMsg(errorMsg, 'error');
                btn.disabled = false;
                btn.textContent = 'Secure Login';
            }
        }
    </script>
</body>
</html>
