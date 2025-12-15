<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$old = $_SESSION['register_old'] ?? ['email' => '', 'username' => ''];
unset($_SESSION['register_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Chandusoft</title>
<link rel="stylesheet" href="/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />

<?php include __DIR__ . '/../admin/header.php'; ?>

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
main.register-page {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

/* Modern Form Container */
.register-form {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.08);
    animation: fadeInUp 0.6s ease;
    box-sizing: border-box;
}

@keyframes fadeInUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.register-page h2 {
    text-align: center;
    color: #1E90FF;
    margin-bottom: 25px;
    font-weight: 600;
}

/* Labels */
.register-form label {
    text-align: left;
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}


/* Inputs */
.register-form input {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    background: rgba(255,255,255,0.9);
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.register-form input:focus {
    border-color: #0078D7;
    outline: none;
    box-shadow: 0 0 6px 2px rgba(0,120,215,0.2);
    background: #fff;
}

/* Submit Button */
.register-form button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0078D7, #1E90FF);
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,120,215,0.2);
}

.register-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,120,215,0.3);
}

/* Link */
.link {
    text-align: center;
    margin-top: 15px;
}
.link a {
    color: #007BFF;
    text-decoration: none;
}
.link a:hover {
    text-decoration: none;
}

/* Toast message */
.alert {
    position: fixed;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%) translateY(40px);
    min-width: 320px;
    padding: 14px 22px;
    border-radius: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-align: center;
    z-index: 9999;
    opacity: 0;
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.5);
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.5s ease;
}

.alert.success { color: #155724; }
.alert.error { color: #721c24; }

.alert.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}
/* ===== ICON INPUTS (same as login) ===== */
.input-icon-group {
    position: relative;
    width: 100%;
}

/* Text alignment same as login button */
.input-icon-group input {
    width: 100%;
    padding: 10px 0;
    text-indent: 38px; /* space for left icon */
    margin-bottom: 18px;
}

/* Add space for right icon in password fields */
.password-wrapper input {
    padding-right: 38px;
}

/* Left icons */
.field-icon-left {
    position: absolute;
    top: 35%;
    left: 12px;
    transform: translateY(-50%);
    font-size: 16px;
    color: #555;
    pointer-events: none;
}

/* Right icons (eye toggle) */
.field-icon-right {
    position: absolute;
    top: 35%;
    right: 12px;
    transform: translateY(-50%);
    font-size: 18px;
    color: #555;
    cursor: pointer;
}

/* Dark mode support */
body.dark-mode .field-icon-left,
body.dark-mode .field-icon-right {
    color: #ccc;
}

</style>
</head>

<body>

<main class="register-page">
    <form class="register-form" action="register_handler.php" method="POST">

       <h2>Create an Account</h2>

<input type="hidden" name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<!-- Email -->
  <label>Email Address</label>
<div class="input-icon-group">
    <i class="bi bi-envelope field-icon-left"></i>
    <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>"
           placeholder="Enter your email" required>
</div>

<!-- Username -->
  <label>User Name</label>
<div class="input-icon-group">
    <i class="bi bi-person field-icon-left"></i>
    <input type="text" name="username" value="<?= htmlspecialchars($old['username']) ?>"
           placeholder="Choose a username" required>
</div>

<!-- Password -->
  <label>Password</label>
<div class="input-icon-group password-wrapper">
    <i class="bi bi-key-fill field-icon-left"></i>
    <input type="password" id="password" name="password" placeholder="Create a password" required>
    <i class="bi bi-eye-slash field-icon-right" id="togglePass1"></i>
</div>

<!-- Confirm Password -->
  <label>Confirm Password</label>
<div class="input-icon-group password-wrapper">
    <i class="bi bi-shield-lock-fill field-icon-left"></i>
    <input type="password" id="confirm_password" name="confirm_password"
           placeholder="Confirm your password" required>
    <i class="bi bi-eye-slash field-icon-right" id="togglePass2"></i>
</div>

<button type="submit">Register</button>

<div class="link">
    <p>Already have an account?
        <a href="../admin/login.php">Login here</a>
    </p>
</div>

    </form>
</main>

<?php if (!empty($successMessage)): ?>
    <div class="alert success" id="formMessage">
        ✅ <?= htmlspecialchars($successMessage) ?>
    </div>
<?php elseif (!empty($errorMessage)): ?>
    <div class="alert error" id="formMessage">
        ❌ <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>

<script>
window.addEventListener("DOMContentLoaded", () => {
    const msg = document.getElementById("formMessage");
    if (msg) {
        setTimeout(() => msg.classList.add("show"), 200);
        setTimeout(() => {
            msg.style.transform = "translateX(-50%) translateY(30px)";
            msg.style.opacity = "0";
        }, 4000);
        setTimeout(() => msg.remove(), 4500);
    }
});

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    icon.addEventListener("click", () => {
        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        icon.classList.toggle("bi-eye");
        icon.classList.toggle("bi-eye-slash");
    });
}

togglePassword("password", "togglePass1");
togglePassword("confirm_password", "togglePass2");


</script>

<?php include __DIR__ . '/../admin/footer.php'; ?>

<button id="back-to-top" title="Back to Top">↑</button>
<script src="/include.js"></script>

</body>
</html>
