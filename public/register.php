<?php
// register.php — Registration with Firebase Email Verification + Google Sign-Up
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();

// Handle server-side registration (after Firebase email user is created)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($phone && !preg_match('/^[6-9]\d{9}$/', $phone)) {
        $error = "Phone number must be a valid 10-digit Indian number.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($db->findUserByEmail($email)) {
        $error = "This email is already registered. Try logging in.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->createUser($name, $email, $hash, 'user', $phone);
        $_SESSION['flash_success'] = "🎉 Registration successful! Check your email for verification link, then login.";
        header("Location: /login.php");
        exit;
    }
}
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<style>
.register-card {
    background: var(--surface);
    max-width: 460px;
    margin: 2rem auto 3rem;
    border-radius: 1.5rem;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.register-header {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    padding: 2rem 2rem 1.5rem;
    color: white;
    text-align: center;
}

.register-header h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
.register-header p { font-size: 0.85rem; opacity: 0.85; }

.register-body { padding: 2rem; }

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

.phone-input-group { display: flex; align-items: center; gap: 0.5rem; }
.phone-prefix { background: var(--background); padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: var(--text-light); font-weight: 500; font-size: 0.95rem; }

.auth-msg {
    text-align: center; font-size: 0.85rem; font-weight: 500; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 1rem;
}
.auth-msg.error { background: #fef2f2; color: #dc2626; }
.auth-msg.success { background: #f0fdf4; color: #16a34a; }

.optional-tag { font-size: 0.75rem; color: var(--text-light); font-weight: 400; }
</style>

<div class="register-card">
    <div class="register-header">
        <h2>Create Account</h2>
        <p>Join RentRide today</p>
    </div>

    <div class="register-body">
        <?php if (isset($error)): ?>
            <div class="auth-msg error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Google Sign-Up -->
        <button type="button" class="btn-google" id="googleSignUpBtn" onclick="signUpWithGoogle()">
            <svg viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Sign up with Google
        </button>
        <div id="googleMsg" class="auth-msg" style="display: none;"></div>

        <div class="divider"><span>or register with email</span></div>

        <!-- Registration Form -->
        <form id="registerForm" onsubmit="return handleRegister(event)">
            <div class="form-group">
                <label>Full Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="regName" class="form-control" required placeholder="e.g. Akash Kumar">
            </div>
            <div class="form-group">
                <label>Email Address <span style="color: #ef4444;">*</span></label>
                <input type="email" id="regEmail" class="form-control" required placeholder="e.g. akash@gmail.com">
            </div>
            <div class="form-group">
                <label>Mobile Number <span class="optional-tag">(optional)</span></label>
                <div class="phone-input-group">
                    <span class="phone-prefix">+91</span>
                    <input type="tel" id="regPhone" class="form-control" placeholder="9876543210" maxlength="10" pattern="[6-9][0-9]{9}">
                </div>
                <small style="color: var(--text-light); font-size: 0.75rem;">Must be a valid 10-digit number if provided</small>
            </div>
            <div class="form-group">
                <label>Password <span style="color: #ef4444;">*</span></label>
                <input type="password" id="regPassword" class="form-control" required placeholder="Min 6 characters" minlength="6">
            </div>
            <div id="regMsg" class="auth-msg" style="display: none;"></div>
            <button type="submit" class="btn btn-primary" id="regBtn" style="width: 100%; padding: 0.75rem; margin-top: 0.5rem;">
                Create Account
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.25rem; font-size: 0.9rem;">
            Already have an account? <a href="/login.php" style="color: var(--primary); font-weight: 600;">Login</a>
        </p>
    </div>
</div>

<!-- Hidden form for server submission -->
<form id="serverRegForm" method="POST" style="display: none;">
    <input type="hidden" name="register" value="1">
    <input type="hidden" name="name" id="srvName">
    <input type="hidden" name="email" id="srvEmail">
    <input type="hidden" name="phone" id="srvPhone">
    <input type="hidden" name="password" id="srvPassword">
</form>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
<script src="/assets/js/firebase-config.js"></script>

<script>
// ===================== EMAIL/PASSWORD REGISTRATION =====================
function handleRegister(e) {
    e.preventDefault();
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const phone = document.getElementById('regPhone').value.trim();
    const password = document.getElementById('regPassword').value;
    const msgEl = document.getElementById('regMsg');
    const btn = document.getElementById('regBtn');

    // Client-side validation
    if (!name) { showMsg(msgEl, 'Please enter your name.', 'error'); return false; }
    if (!email) { showMsg(msgEl, 'Please enter your email.', 'error'); return false; }
    if (phone && !/^[6-9]\d{9}$/.test(phone)) { showMsg(msgEl, 'Phone must be a valid 10-digit number.', 'error'); return false; }
    if (password.length < 6) { showMsg(msgEl, 'Password must be at least 6 characters.', 'error'); return false; }

    btn.disabled = true;
    btn.textContent = 'Creating account...';
    msgEl.style.display = 'none';

    // Step 1: Create Firebase Auth user
    firebase.auth().createUserWithEmailAndPassword(email, password)
        .then((userCredential) => {
            // Step 2: Send verification email
            return userCredential.user.sendEmailVerification().then(() => {
                // Sign out from Firebase
                firebase.auth().signOut();
                
                // Step 3: Submit to our server to create user in JSON DB
                document.getElementById('srvName').value = name;
                document.getElementById('srvEmail').value = email;
                document.getElementById('srvPhone').value = phone;
                document.getElementById('srvPassword').value = password;
                document.getElementById('serverRegForm').submit();
            });
        })
        .catch((error) => {
            console.error("Firebase Registration Error Details:", error);
            btn.disabled = false;
            btn.textContent = 'Create Account';
            
            let msg = 'Registration failed.';
            if (error.code === 'auth/internal-error') {
                msg = "Firebase Internal Error. Check browser console for details.";
            } else {
                switch(error.code) {
                    case 'auth/email-already-in-use':
                        msg = 'This email is already registered. Try logging in.';
                        break;
                    case 'auth/weak-password':
                        msg = 'Password is too weak. Use at least 6 characters.';
                        break;
                    case 'auth/invalid-email':
                        msg = 'Please enter a valid email address.';
                        break;
                    default:
                        msg = error.message;
                }
            }
            showMsg(msgEl, msg, 'error');
        });

    return false;
}

// ===================== GOOGLE SIGN-UP =====================
function signUpWithGoogle() {
    const btn = document.getElementById('googleSignUpBtn');
    const msgEl = document.getElementById('googleMsg');
    btn.disabled = true;
    btn.style.opacity = '0.7';
    msgEl.style.display = 'none';

    const provider = new firebase.auth.GoogleAuthProvider();
    firebase.auth().signInWithPopup(provider)
        .then((result) => {
            const user = result.user;
            showMsg(msgEl, '✅ Creating your account...', 'success');

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
                showMsg(document.getElementById('googleMsg'), data.error || 'Registration failed', 'error');
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        })
        .catch((error) => {
            console.error("Firebase Google Sign-Up Error Details:", error);
            if (error.code === 'auth/popup-closed-by-user') { btn.disabled = false; btn.style.opacity = '1'; return; }
            let msg = error.message || 'Google Sign-Up failed.';
            if (error.code === 'auth/internal-error') {
                msg = "Firebase Internal Error. Check browser console for details.";
            }
            showMsg(document.getElementById('googleMsg'), msg, 'error');
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
