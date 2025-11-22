<?php
session_start();
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $otp = random_int(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_exp'] = time() + 300;

        mail($email, "Your Password Reset OTP", "Your OTP is: $otp");

        $_SESSION['flash_success'] = "OTP sent to your email.";
        header("Location: /app/verify-otp.php");
        exit();
    } else {
        $_SESSION['flash_error'] = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">

<style>
body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg, #eef3ff, #e1ecff);
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Card */
.form-box {
    width: 100%;
    max-width: 420px;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(10px);
    padding: 40px 35px;
    border-radius: 18px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.form-box h2 {
    text-align: center;
    color: #1E90FF;
    margin-bottom: 25px;
    font-weight: 600;
}

/* Inputs */
form input {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border-radius: 10px;
    border: 1px solid #bbb;
    font-size: 15px;
    transition: 0.3s ease;
}

form input:focus {
    border-color: #1E90FF;
    box-shadow: 0 0 5px rgba(30,144,255,0.4);
    outline: none;
}

/* Button */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0078D7, #1E90FF);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,120,215,0.3);
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,120,215,0.4);
}

/* Message */
.message {
    text-align: center;
    font-size: 14px;
    margin-bottom: 15px;
}

.message.error { color: #c0392b; }
.message.success { color: #27ae60; }

/* Back link */
.back-link {
    text-align: center;
    margin-top: 15px;
}

.back-link a {
    color: #007BFF;
    text-decoration: none;
    font-size: 14px;
}

.back-link a:hover {
    text-decoration: underline;
}

</style>
</head>
<body>

<div class="form-box">
    <h2>Forgot Password</h2>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <p class="message error"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit">Send OTP</button>
    </form>

    <div class="back-link">
        <a href="/admin/login.php">← Back to Login</a>
    </div>
</div>

</body>
</html>
