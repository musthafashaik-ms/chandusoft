<?php
// ============================================================
// Chandusoft Admin/User Login Page - Modern UI Version (Dark Mode + Google Button + Mobile Optimized)
// ============================================================

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
session_set_cookie_params([
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF TOKEN
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session Timeout
$timeout_duration = 1800; // 30 mins
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /admin/login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Chandusoft - Login</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
<link rel="stylesheet" href="/styles.css" />

<?php include __DIR__ . '/header.php'; ?>

<style>
/* ---------------- VARIABLES ---------------- */
:root {
    --bg-light: #eef3ff;
    --bg-dark: #000000;
    --card-light: rgba(255,255,255,0.95);
    --card-dark: #111111;
    --input-light: rgba(255,255,255,0.9);
    --input-dark: #1a1a1a;
    --text-light: #333;
    --text-dark: #fff;
    --primary-color: #1e90ff;
    --google-btn-bg: #fff;
    --google-btn-text: #000;
}

/* ---------------- BODY ---------------- */
body {
    font-family: "Poppins", Arial, sans-serif;
    background: var(--bg-light);
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: background 0.3s ease, color 0.3s ease;
}

body.dark-mode {
    background: var(--bg-dark);
    color: var(--text-dark);
}

/* ---------------- LOGIN PAGE ---------------- */
main.login-page {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

.login-form {
    width: 100%;
    max-width: 420px;
    background: var(--card-light);
    backdrop-filter: blur(12px);
    padding: 40px;
    border-radius: 22px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.1);
    animation: fadeInUp 0.6s ease;
    transition: background 0.3s ease, color 0.3s ease;
}

body.dark-mode .login-form {
    background: var(--card-dark);
}

/* ---------------- HEADINGS ---------------- */
.login-form h2 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 25px;
    font-weight: 600;
}

/* ---------------- INPUTS ---------------- */
.login-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: var(--text-light);
}

body.dark-mode .login-form label {
    color: var(--text-dark);
}

.login-form input {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    background: var(--input-light);
    transition: all 0.3s ease;
}

body.dark-mode .login-form input {
    background: var(--input-dark);
    border: 1px solid #333;
    color: var(--text-dark);
}

.login-form input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 6px 2px rgba(30,144,255,0.2);
}

/* ---------------- PASSWORD ---------------- */
.password-wrapper {
    position: relative;
}

.password-wrapper i {
    position: absolute;
    top: 30%;
    right: 12px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #555;
}

/* Caps Lock */
#capsWarning {
    display: none;
    color: red;
    font-size: 13px;
    margin-top: -12px;
    margin-bottom: 10px;
}

/* ---------------- REMEMBER & FORGOT ---------------- */
.login-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 5px;
    margin-bottom: 20px;
    width: 100%;
}
/* Make checkbox bigger */
.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    transform: scale(1.2); /* slightly bigger */
    cursor: pointer; 
    margin-top: 8px; /* ↓ This moves checkbox slightly downward */
}

/* Fix alignment perfectly */
.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap; /* prevents wrapping */
}

.remember-me label {
    margin-top: 1px; /* aligns text vertically */
    font-size: 14px;
    cursor: pointer;
}



body.dark-mode .remember-me label {
    color: #ccc;
}

.forgot-link {
    font-size: 14px;
    color: #666;
    text-decoration: none;
}

body.dark-mode .forgot-link {
    color: #ccc;
}

.forgot-link:hover {
    text-decoration: none;
}

/* ---------------- LOGIN BUTTON ---------------- */
.login-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0078d7, #1e90ff);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0,120,215,0.2);
    transition: 0.3s ease;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,120,215,0.3);
}

/* ---------------- DIVIDER ---------------- */
.divider {
    display: flex;
    align-items: center;
    margin: 20px 0 10px;
    font-size: 13px;
    color: #80868b;
}

.divider::before,
.divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background-color: #e0e0e0;
}

.divider span {
    margin: 0 10px;
}

/* ---------------- GOOGLE BUTTON ---------------- */
.google-btn {
    width: 100%;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 16px;
    background-color: var(--google-btn-bg);
    border: 1px solid #dadce0;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    color: var(--google-btn-text);
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.google-btn:hover {
    background-color: #f7f7f7;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.google-btn img {
    width: 20px;
    height: 20px;
}

/* ---------------- FOOTER ---------------- */
.login-bottom {
    text-align: center;
    margin-top: 15px;
}

.login-bottom a {
    color: #007bff;
    text-decoration: none;
}

body.dark-mode .login-bottom a {
    color: #1e90ff;
}

.login-bottom a:hover {
    text-decoration: underline;
}

/* ---------------- TOAST ---------------- */
.alert {
    position: fixed;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%) translateY(40px);
    min-width: 320px;
    padding: 14px 22px;
    border-radius: 12px;
    font-weight: 500;
    text-align: center;
    opacity: 0;
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    z-index: 9999;
    transition: 0.5s ease;
}

.alert.success { color: #155724; }
.alert.error { color: #721c24; }

.alert.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* ---------------- ANIMATION ---------------- */
@keyframes fadeInUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ---------------- DARK MODE TOGGLE ---------------- */
#darkToggle {
    position: fixed;
    bottom: 15px;
    left: 15px;
    background: #1e90ff;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 6px 12px;
    cursor: pointer;
    font-weight: 500;
    z-index: 999;
    transition: background 0.3s ease;
}


#darkToggle:hover {
    background: #0078d7;
}

/* ---------------- RESPONSIVE ---------------- */
@media (max-width: 480px) {
    .login-form { padding: 30px 20px; border-radius: 18px; }
    .login-btn, .google-btn { padding: 10px; font-size: 14px; }
}
</style>
</head>

<body>
<!-- DARK MODE TOGGLE -->
<button id="darkToggle">Dark Mode</button>

<main class="login-page">
<form class="login-form" action="/app/authenticate.php" method="POST">
    <h2>Login</h2>

    <!-- FLASH ERROR -->
    <?php if (!empty($_SESSION['login_errors'])) : ?>
        <div class="alert error" id="formMessage">
            ❌ <?= htmlspecialchars(implode('<br>', $_SESSION['login_errors'])) ?>
        </div>
        <?php unset($_SESSION['login_errors']); ?>
    <?php endif; ?>

    <!-- FLASH SUCCESS -->
    <?php if (!empty($_SESSION['flash_success'])) : ?>
        <div class="alert success" id="formMessage">
            ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <label>Email Address</label>
    <input type="email" name="email" value="<?= htmlspecialchars($old_email) ?>" placeholder="Enter your email" required />

    <label>Password</label>
    <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Enter your password" required />
        <i class="bi bi-eye-slash" id="toggleEye"></i>
    </div>

    <div id="capsWarning">⚠ Caps Lock is ON</div>

    <div class="login-row">
        <div class="remember-me">
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Remember Me</label>
        </div>
        <a href="/app/forgot-password.php" class="forgot-link">Forgot Password?</a>
    </div>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />

    <button type="submit" class="login-btn">Login</button>

    <div class="divider"><span>or</span></div>

    <!-- GOOGLE BUTTON -->
    <button type="button" class="google-btn" onclick="window.location.href='/app/google-login.php'">
        <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
        Continue with Google
    </button>

    <div class="login-bottom">
        <p>Don’t have an account? <a href="/app/register">Create one</a></p>
    </div>
</form>
</main>

<script>
/* ---------------- DARK MODE TOGGLE ---------------- */
const darkToggle = document.getElementById('darkToggle');
const body = document.body;

darkToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    darkToggle.textContent = body.classList.contains('dark-mode') ? 'Light Mode' : 'Dark Mode';
});

/* ---------------- TOAST ---------------- */
window.addEventListener("DOMContentLoaded", () => {
    const msg = document.getElementById("formMessage");
    if (msg) {
        setTimeout(() => msg.classList.add("show"), 150);
        setTimeout(() => {
            msg.style.opacity = "0";
            msg.style.transform = "translateX(-50%) translateY(20px)";
        }, 4000);
        setTimeout(() => msg.remove(), 4500);
    }
});

/* ---------------- SHOW/HIDE PASSWORD ---------------- */
const pw = document.getElementById("password");
const toggleEye = document.getElementById("toggleEye");

toggleEye.addEventListener("click", () => {
    const isHidden = pw.type === "password";
    pw.type = isHidden ? "text" : "password";
    toggleEye.classList.toggle("bi-eye");
    toggleEye.classList.toggle("bi-eye-slash");
});

/* ---------------- CAPS LOCK ---------------- */
pw.addEventListener("keyup", (event) => {
    const caps = event.getModifierState("CapsLock");
    document.getElementById("capsWarning").style.display = caps ? "block" : "none";
});
</script>

<?php include __DIR__ . '/footer.php'; ?>

<button id="back-to-top" title="Back to Top">↑</button>
<script src="/include.js"></script>
</body>
</html>
