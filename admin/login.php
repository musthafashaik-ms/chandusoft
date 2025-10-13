<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Save the old email input in case of failure
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']); // Clear old email after it's used
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
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
            min-height: 100px;
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
            background: #1E90FF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0077cc;
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
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .register-link button {
            background-color: #28a745;
        }
        .register-link button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login Form</h2>

    <!-- Display login errors -->
    <?php
    if (!empty($_SESSION['login_errors'])) {
        foreach ($_SESSION['login_errors'] as $error) {
            echo "<p class='message error'>" . htmlspecialchars($error) . "</p>";
        }
        unset($_SESSION['login_errors']);
    }

    if (isset($_SESSION['flash_success'])) {
        echo "<p class='message success'>" . htmlspecialchars($_SESSION['flash_success']) . "</p>";
        unset($_SESSION['flash_success']);
    }
    ?>

    <form action="../app/authenticate.php" method="POST">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($old_email) ?>" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <button type="submit">Login</button>
    </form>

    <!-- Register Button -->
    <div class="register-link">
        <p>Don't have an account?</p>
        <a href="../app/register.php">
            <button type="button">Register</button>
        </a>
    </div>
</div>

</body>
</html>
