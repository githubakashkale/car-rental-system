<?php
// login.php — Login with Firebase Email Verification + Google Sign-In
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/admin.php';
session_start();

// Flash message from registration
$flash = '';
if (isset($_SESSION['flash_success'])) {
    $flash = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Server-side login (called via AJAX after Firebase email is verified)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['firebase_login'])) {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->findUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        // Check blacklist status
        if (($user['role'] ?? 'user') === 'blacklisted') {
            echo json_encode(['success' => false, 'error' => '🚫 Your account has been blacklisted due to major vehicle damage. Please contact support.']);
            exit;
        }

        // Enforce single admin: only cars.rentride@gmail.com can be admin
        $role = isAdminEmail($email) ? 'admin' : ($user['role'] === 'admin' ? 'user' : $user['role']);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $role;
        session_write_close();
        echo json_encode([
            'success' => true,
            'redirect' => $role === 'admin' ? '/admin/dashboard.php' : '/'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    }
    exit;
}
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<style>
.login-card {
    background: var(--surface);
    max-width: 460px;
    margin: 2rem auto 3rem;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.login-header {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    padding: 2rem 2rem 1.5rem;
    color: white;
    text-align: center;
}
.login-header h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
.login-header p { font-size: 0.85rem; opacity: 0.85; }

.login-body { padding: 2rem; }

.btn-google {
    width: 100%; padding: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem;
    background: white; border: 2px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.95rem; font-weight: 600;
    color: #374151; cursor: pointer; transition: all 0.2s; font-family: 'Outfit', sans-serif;
}
.btn-google:hover { border-color: #4285f4; box-shadow: 0 2px 8px rgba(66, 133, 244, 0.15); transform: translateY(-1px); }
.btn-google:active { transform: translateY(0); }
.btn-google svg { width: 20px; height: 20px; }

.divider { display: flex; align-items: center; margin: 1.5rem 0; gap: 1rem; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
.divider span { font-size: 0.8rem; color: var(--text-light); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }

.auth-msg {
    text-align: center; font-size: 0.85rem; font-weight: 500; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 1rem;
}
.auth-msg.error { background: #fef2f2; color: #dc2626; }
.auth-msg.success { background: #f0fdf4; color: #16a34a; }
.auth-msg.warning { background: #fffbeb; color: #d97706; }

.resend-link {
    display: inline-block; margin-top: 0.5rem; color: var(--primary); font-weight: 600; cursor: pointer;
    text-decoration: underline; font-size: 0.85rem; background: none; border: none; font-family: inherit;
}
</style>

<div class="login-card">
    <div class="login-header">
        <h2>Welcome Back</h2>
        <p>Sign in to your RentRide account</p>
    </div>

    <div class="login-body">
        <?php if ($flash): ?>
            <div class="auth-msg success"><?= $flash ?></div>
        <?php endif; ?>

        <!-- Google Sign-In -->
        <button type="button" class="btn-google" id="googleSignInBtn" onclick="signInWithGoogle()">
            <svg viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Sign in with Google
        </button>
        <div id="googleMsg" class="auth-msg" style="display: none;"></div>

        <div class="divider"><span>or login with email</span></div>

        <!-- Email/Password Login Form -->
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="loginEmail" class="form-control" required placeholder="e.g. akash@gmail.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="loginPassword" class="form-control" required placeholder="Enter your password">
            </div>
            <div id="loginMsg" class="auth-msg" style="display: none;"></div>
            <button type="submit" class="btn btn-primary" id="loginBtn" style="width: 100%; padding: 0.75rem;">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 1.25rem; font-size: 0.9rem;">
            Don't have an account? <a href="/register.php" style="color: var(--primary); font-weight: 600;">Register</a>
        </p>
    </div>
</div>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="/assets/js/firebase-config.js"></script>

<script>
// ===================== EMAIL/PASSWORD LOGIN =====================
function handleLogin(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const msgEl = document.getElementById('loginMsg');
    const btn = document.getElementById('loginBtn');

    if (!email || !password) { showMsg(msgEl, 'Please fill in both fields.', 'error'); return false; }

    btn.disabled = true;
    btn.textContent = 'Signing in...';
    msgEl.style.display = 'none';

    // Step 1: Sign in with Firebase to check email verification
    firebase.auth().signInWithEmailAndPassword(email, password)
        .then((userCredential) => {
            const user = userCredential.user;
            
            // Step 2: Check if email is verified
            if (!user.emailVerified) {
                // Email NOT verified — block login
                showMsg(msgEl, '📧 Please verify your email first! Check your inbox for the verification link.', 'warning');
                
                // Add resend button
                const existingResend = document.getElementById('resendBtn');
                if (!existingResend) {
                    const resendBtn = document.createElement('button');
                    resendBtn.id = 'resendBtn';
                    resendBtn.className = 'resend-link';
                    resendBtn.textContent = '📩 Resend verification email';
                    resendBtn.onclick = function() {
                        user.sendEmailVerification().then(() => {
                            showMsg(msgEl, '✅ Verification email resent! Check your inbox.', 'success');
                        }).catch(() => {
                            showMsg(msgEl, 'Please wait a minute before resending.', 'warning');
                        });
                    };
                    msgEl.parentNode.insertBefore(resendBtn, msgEl.nextSibling);
                }
                
                firebase.auth().signOut();
                btn.disabled = false;
                btn.textContent = 'Sign In';
                return;
            }

            // Step 3: Email IS verified — proceed to server login
            firebase.auth().signOut();
            showMsg(msgEl, '✅ Email verified! Logging you in...', 'success');

            // Submit to our server
            fetch('/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `firebase_login=1&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    showMsg(msgEl, data.error || 'Login failed.', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Sign In';
                }
            })
            .catch(() => {
                showMsg(msgEl, 'Server error. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Sign In';
            });
        })
        .catch((error) => {
            btn.disabled = false;
            btn.textContent = 'Sign In';
            
            let msg = 'Login failed.';
            switch(error.code) {
                case 'auth/user-not-found':
                case 'auth/wrong-password':
                case 'auth/invalid-credential':
                    msg = 'Invalid email or password.';
                    break;
                case 'auth/too-many-requests':
                    msg = 'Too many attempts. Please try again later.';
                    break;
                case 'auth/invalid-email':
                    msg = 'Please enter a valid email address.';
                    break;
                default:
                    msg = error.message;
            }
            showMsg(msgEl, msg, 'error');
        });

    return false;
}

// ===================== GOOGLE SIGN-IN =====================
function signInWithGoogle() {
    const btn = document.getElementById('googleSignInBtn');
    const msgEl = document.getElementById('googleMsg');
    btn.disabled = true;
    btn.style.opacity = '0.7';
    msgEl.style.display = 'none';

    const provider = new firebase.auth.GoogleAuthProvider();
    firebase.auth().signInWithPopup(provider)
        .then((result) => {
            const user = result.user;
            showMsg(msgEl, '✅ Signing you in...', 'success');

            return fetch('/google_auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `name=${encodeURIComponent(user.displayName || '')}&email=${encodeURIComponent(user.email || '')}&phone=&photo=${encodeURIComponent(user.photoURL || '')}`
            });
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                firebase.auth().signOut();
                showMsg(document.getElementById('googleMsg'), data.message, 'success');
                setTimeout(() => { window.location.href = data.redirect; }, 800);
            } else {
                showMsg(document.getElementById('googleMsg'), data.error || 'Login failed', 'error');
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        })
        .catch((error) => {
            if (error.code === 'auth/popup-closed-by-user') { btn.disabled = false; btn.style.opacity = '1'; return; }
            showMsg(document.getElementById('googleMsg'), error.message || 'Google Sign-In failed.', 'error');
            btn.disabled = false;
            btn.style.opacity = '1';
        });
}

function showMsg(el, text, type) {
    el.textContent = text;
    el.className = 'auth-msg ' + type;
    el.style.display = 'block';
}
</script>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
