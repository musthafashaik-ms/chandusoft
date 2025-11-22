<?php
session_start();
require __DIR__ . '/config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];

    if ($otp == $_SESSION['otp'] && time() < $_SESSION['otp_exp']) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset-password.php");
        exit();
    } else {
        $_SESSION['flash_error'] = "Invalid or expired OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
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

      .form-box {
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 40px 35px;
        border-radius: 18px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
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
        box-shadow: 0 0 5px rgba(30, 144, 255, 0.4);
        outline: none;
      }

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
        box-shadow: 0 4px 12px rgba(0, 120, 215, 0.3);
      }
      button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 120, 215, 0.4);
      }

      .message {
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
      }
      .message.error { color: #c0392b; }

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
    <h2>Verify OTP</h2>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <p class="message error"><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></p>
    <?php endif; ?>

    <form method="POST">
      <label>Enter OTP sent to your email</label>
      <input type="number" name="otp" placeholder="6-digit code" required>
      <button type="submit">Verify</button>
    </form>

    <div class="back-link">
      <a href="forgot-password.php">← Back to Forgot Password</a>
    </div>
  </div>

</body>
</html>
