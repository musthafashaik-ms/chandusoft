<?php
session_start();
 
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/vendor/autoload.php';
 
$pageSlug = 'contact';
$formData = ['name' => '', 'email' => '', 'message' => ''];
 
// ✅ Flash messages
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage = $_SESSION['errorMessage'] ?? '';
unset($_SESSION['successMessage'], $_SESSION['errorMessage']);
 
// ✅ Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
 
    $formData = ['name' => $name, 'email' => $email, 'message' => $message];
 
    if ($name && $email && $message) {
        try {
            // ✅ Insert into Database
            $stmt = $pdo->prepare("INSERT INTO leads (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $message]);
 
            // ✅ Send Email
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
               $environment = 'development'; // or 'production'
               if ($environment === 'development') {
              // Use Mailpit (local)
              $mail->isSMTP();
              $mail->Host = 'localhost';
              $mail->Port = 1025;
              $mail->SMTPAuth = false;
              $mail->SMTPSecure = false;
            }
            else {
              // Use Gmail (production)
              $mail->isSMTP();
              $mail->Host = 'smtp.gmail.com';
              $mail->SMTPAuth = true;
              $mail->Username = 'cstltest4@gmail.com';
              $mail->Password = 'vwrs cubq qpqg wfcg';
              $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
              $mail->Port = 587;
            }
            // ✅ Make sure UTF-8 is used for emojis/icons
              $mail->CharSet = 'UTF-8';
              $mail->Encoding = 'base64'; // optional but recommended for emojis

            $mail->setFrom('cstltest4@gmail.com', 'Chandusoft Contact');
            $mail->addAddress('musthafa.shaik@chandusoft.com');
            $mail->addReplyTo($email, $name);
 
            $mail->isHTML(true);
            $mail->Subject ="🚀New Contact Form Submission";
            $mail->Body = "
                    <h3>New Contact Form Message</h3>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
                ";
 
                $mail->send();
            } catch (Exception $e) {
                error_log("Mail Error: " . $mail->ErrorInfo);
            }
 
            $_SESSION['successMessage'] = "Your message has been sent successfully!";
            header("Location: /contact");
            exit;
        } catch (Exception $e) {
            $_SESSION['errorMessage'] = "Something went wrong. Please try again.";
            header("Location: /contact");
            exit;
        }
    } else {
        $_SESSION['errorMessage'] = "All fields are required!";
        header("Location: /contact");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chandusoft - Contact</title>
<link rel="stylesheet" href="/styles.css">
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
 
/* ✅ Centered Layout */
main.contact-page {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
}

/* ✅ Modern Form Design */
.contact-form {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 40px 40px;
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
    animation: fadeInUp 0.6s ease;
    box-sizing: border-box; /* keeps borders even */
    overflow: hidden; /* prevents inner overflow */
}

@keyframes fadeInUp {
    from { transform: translateY(25px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.contact-page h2 {
    text-align: center;
    color: #0078D7;
    margin-bottom: 25px;
    font-weight: 600;
}

/* ✅ Label Styling */
.contact-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

/* ✅ Input & Textarea Fields */
.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.9);
    transition: all 0.3s ease;
    box-sizing: border-box; /* ensures even border spacing */
}

/* ✅ Textarea: only vertical resize */
.contact-form textarea {
    resize: vertical; /* ✅ allow only up/down resizing */
    min-height: 120px;
    max-height: 300px;
    overflow: auto; /* scrolls inside when text is long */
}

/* ✅ Focus Style (balanced on all sides) */
.contact-form input:focus,
.contact-form textarea:focus {
    border-color: #0078D7;
    outline: none;
    box-shadow: 0 0 6px 2px rgba(0, 120, 215, 0.2); /* even glow */
    background: #fff;
}

/* ✅ Submit Button */
.contact-form button {
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
    box-shadow: 0 3px 10px rgba(0, 120, 215, 0.2);
}

.contact-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 120, 215, 0.3);
}

 
/* ✅ Toast (bottom-center with iOS blur) */
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
 
.alert svg {
    width: 20px;
    height: 20px;
}
 
.alert.success {
    color: #155724;
}
 
.alert.error {
    color: #721c24;
}
 
.alert.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}
</style>
</head>
<body>
 
<?php include("admin/header.php"); ?>
 
<main class="contact-page">
    <form class="contact-form" action="/contact" method="post">
        <h2>Contact Form</h2>
 
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($formData['name']) ?>" placeholder="Enter your name" required>
 
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($formData['email']) ?>" placeholder="Enter your email" required>
 
        <label for="message">Message</label>
        <textarea name="message" id="message" rows="5" placeholder="Write your message..." required><?= htmlspecialchars($formData['message']) ?></textarea>
 
        <button type="submit">Send Message</button>
    </form>
</main>
 
<?php if ($successMessage): ?>
    <div class="alert success" id="formMessage">
        ✅ <?= htmlspecialchars($successMessage) ?>
    </div>
<?php elseif ($errorMessage): ?>
    <div class="alert error" id="formMessage">
        ❌ <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>
 
<script>
window.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('formMessage');
    if (msg) {
        setTimeout(() => msg.classList.add('show'), 150);
        setTimeout(() => {
            msg.style.transform = 'translateX(-50%) translateY(30px)';
            msg.style.opacity = '0';
        }, 4000);
        setTimeout(() => msg.remove(), 4500);
    }
});
</script>
 
<?php include("admin/footer.php"); ?>
<button id="back-to-top" title="Back to Top">↑</button>
<script src="include.js"></script>
</body>
</html>
 
 