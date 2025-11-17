<?php
// ============================================================
// Chandusoft Admin/User Login Page - Modern UI Version
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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chandusoft - Login</title>
  <link rel="stylesheet" href="/styles.css">

<?php include __DIR__ . '/header.php'; ?>

<style>
body {
    font-family: "Poppins", Arial, sans-serif;
    background: linear-gradient(135deg, #eef3ff, #e1ecff);
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Center Layout */
main.login-page {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

/* Modern Glassy Form */
.login-form {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.1);
    animation: fadeInUp 0.6s ease;
    box-sizing: border-box;
}

@keyframes fadeInUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.login-form h2 {
    text-align: center;
    color: #1E90FF;
    margin-bottom: 25px;
    font-weight: 600;
}

/* Labels */
.login-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

/* Inputs */
.login-form input {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    background: rgba(255,255,255,0.9);
    transition: all 0.3s ease;
}

.login-form input:focus {
    border-color: #0078D7;
    outline: none;
    box-shadow: 0 0 6px 2px rgba(0,120,215,0.2);
}

/* Button */
.login-form button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0078D7, #1E90FF);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0,120,215,0.2);
    transition: 0.3s ease;
}

.login-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,120,215,0.3);
}

/* Bottom Link */
.login-bottom {
    text-align: center;
    margin-top: 15px;
}
.login-bottom a {
    color: #007BFF;
    text-decoration: none;
}
.login-bottom a:hover {
    text-decoration: underline;
}

/* Toast Messages */
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
    background: rgba(255,255,255,0.5);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.3);
    z-index: 9999;
    transition: 0.5s ease;
}
.alert.success { color: #155724; }
.alert.error { color: #721c24; }
.alert.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
</head>

<body>

<main class="login-page">
    <form class="login-form" action="/app/authenticate.php" method="POST">
        <h2>Login</h2>

        <!-- PHP Flash Messages Converted to Toast -->
        <?php if (!empty($_SESSION['login_errors'])): ?>
            <div class="alert error" id="formMessage">
                ❌ <?= htmlspecialchars(implode('<br>', $_SESSION['login_errors'])) ?>
            </div>
            <?php unset($_SESSION['login_errors']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert success" id="formMessage">
                ✅ <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <label>Email Address</label>
        <input type="email" name="email" value="<?= htmlspecialchars($old_email) ?>" placeholder="Enter your email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <button type="submit">Login</button>

        <div class="login-bottom">
            <p>Don’t have an account?  
                <a href="/app/register">Create one</a>
            </p>
        </div>
    </form>
</main>

<script>
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
</script>

<?php include __DIR__ . '/footer.php'; ?>

<button id="back-to-top" title="Back to Top">↑</button>
<script src="/include.js"></script>

</body>
</html>
