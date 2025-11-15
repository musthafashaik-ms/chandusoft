<?php

session_start();                  // must be before any output
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$old = $_SESSION['register_old'] ?? ['email'=>'', 'username'=>''];
unset($_SESSION['register_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
<?php include __DIR__ . '/../admin/header.php'; ?>

  <style>
    * {
      box-sizing: border-box; /* include padding + border in width */
    }

     body {
            font-family: Arial, Helvetica, sans-serif;
            color: #030303;
            background-color: #f9f9f9;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }


    .container {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 350px;
    }
.main-content {
            display: flex;
            justify-content: center; /* centers horizontally */
            align-items: flex-start; /* starts from top, not full vertical center */
            flex: 1; /* occupy remaining vertical space */
            padding-top: 5px; /* gap between header and login form */
        }
    h2 {
      text-align: center;
      margin-bottom: 20px;
       color: #007BFF;
    }

    .form-group {
      margin-bottom: 16px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
    }

    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    input:focus {
      border-color: #007BFF;
      box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
      outline: none; /* remove default outline */
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
    /* Header Styles */
        header {
            background-color: #007BFF;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
        }

        .logo img {
            width: 400px;
            height: auto;
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            align-items: center;
        }

        nav a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            color: white;
            position: relative;
            transition: color 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0%;
            height: 3px;
            background-color: #FFD700;
            transition: width 0.3s ease;
        }

        nav a:hover::after,
        nav a.active::after {
            width: 100%;
        }

        nav a:hover,
        nav a.active {
            color: #FFD700;
        }

        /* Footer Styles */
        footer {
            background: #333;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }

        footer p {
            margin: 0;
            font-size: 14px;
        }

        footer p b {
            font-weight: bold;
        }

        .social-icons a {
            color: #fff;
            margin-left:15px;
            font-size: 16px;
            text-decoration: none;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: #1da1f2; /* Hover color */
        }
  </style>
</head>
<body>
    
<div class="main-content">
  <div class="container">
    <h2>Create an Account</h2>
    <form action="register_handler.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($old['email']) ?>" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($old['username']) ?>" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
      </div>

      <button type="submit">Register</button>
    </form>

    <div class="link">
      <p>Already have an account? <a href="../admin/login.php">Login here</a></p>
    </div>
  </div>
  <!-- Include the footer.php from the admin folder -->
<?php include __DIR__ . '/../admin/footer.php'; ?>
</body>


</html>
