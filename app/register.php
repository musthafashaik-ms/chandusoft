<?php
require 'config.php';

// Start session and generate CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Repopulate old input if validation fails
$old = $_SESSION['register_old'] ?? ['email' => '', 'username' => ''];
unset($_SESSION['register_old']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 15px;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #007BFF;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create an Account</h2>

    <?php
    // Show flash messages
    if (!empty($_SESSION['register_errors'])) {
        foreach ($_SESSION['register_errors'] as $error) {
            echo "<p class='message error'>" . htmlspecialchars($error) . "</p>";
        }
        unset($_SESSION['register_errors']);
    }

    if (isset($_SESSION['flash_success'])) {
        echo "<p class='message success'>" . htmlspecialchars($_SESSION['flash_success']) . "</p>";
        unset($_SESSION['flash_success']);
    }
    ?>

    <form action="register_handler.php" method="POST">
        <label for="email">Email</label>
        <input type="text" name="email" id="email" value="<?= htmlspecialchars($old['email']) ?>" required>

        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($old['username']) ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <button type="submit">Register</button>
    </form>

    <div class="link">
        <p>Already have an account? <a href="../admin/login.php">Login here</a></p>
    </div>
</div>
</body>
</html>
