<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Correct path to autoload

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- DB CONNECTION ---
    $conn = new mysqli("127.0.0.1", "root", "", "chandusoft");
    if ($conn->connect_error) {
        echo "❌ Something went wrong with the database connection.";
        exit;
    }

    // --- SANITIZE INPUT ---
    $name = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $email = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $message = trim($conn->real_escape_string($_POST['message'] ?? ''));

    // --- VALIDATE INPUT ---
    if (empty($name) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Invalid input.";
        exit;
    }

    // --- INSERT INTO DB ---
    $sql = "INSERT INTO leads (name, email, message) VALUES ('$name', '$email', '$message')";
    if ($conn->query($sql) === TRUE) {
        // --- SEND EMAIL ---
        $mail = new PHPMailer(true);
        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cstltest4@gmail.com'; // your Gmail
            $mail->Password = 'vwrs cubq qpqg wfcg'; // Gmail App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email headers
            $mail->setFrom('cstltest4@gmail.com', 'Chandusoft Contact Form');
            $mail->addAddress('musthafa.shaik@chandusoft.com', 'Musthafa');
            $mail->addReplyTo($email, $name);

            // Email content
            $mail->isHTML(true);
            $subject = "New Contact Form Submission"; // ✅ Define subject here
            $mail->Subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

            $mail->Body = "
                <h3>🚀New Lead Submission</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
            ";

            $mail->send();
            echo "success";  // Send back success response
            exit;

        } catch (Exception $e) {
            // --- LOG FAILURE ---
            $logDir = __DIR__ . '/../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            $logFile = $logDir . '/mail-fail.log';
            $timestamp = date('Y-m-d H:i:s');
            $errorMessage = "[$timestamp] Mail send failed for {$email} ({$name}). Error: {$mail->ErrorInfo}\nMessage: {$message}\n\n";
            file_put_contents($logFile, $errorMessage, FILE_APPEND);

            echo "❌ Mailer Error: {$mail->ErrorInfo}";  // Provide more details in error response
            exit;
        }
    } else {
        echo "❌ Database insert error.";
        exit;
    }
}
?>





<!-- Your HTML form code goes here (unchanged) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chandusoft - Contact</title>

    <!-- Styles -->
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>
<body>
    <!-- Correct file path to header.php in the admin folder -->
    <?php include __DIR__ . '/admin/header.php'; ?>
    <main>
        <h2>Contact Us</h2>
        <form id="contactForm" class="contact-form" action="contact.php" method="post" novalidate>
            <!-- Name -->
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required />
            <span class="error-message" id="nameError"></span>

            <!-- Email -->
            <label for="email">Your Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required />
            <span class="error-message" id="emailError"></span>

            <!-- Message -->
            <label for="message">Your Message</label>
            <textarea id="message" name="message" rows="5" placeholder="Type your message here..." required></textarea>
            <span class="error-message" id="messageError"></span>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn" disabled>Send Message</button>
        </form>
    </main>

    <?php include __DIR__ . '/admin/footer.php'; ?>


    <!-- Include JS (header, footer, back to top, etc.) -->
    <script src="include.js"></script>

    <!-- Form Validation and Handling Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('contactForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const messageInput = document.getElementById('message');
            const submitBtn = document.getElementById('submitBtn');

            const nameError = document.getElementById('nameError');
            const emailError = document.getElementById('emailError');
            const messageError = document.getElementById('messageError');

            // ✅ Success message element
            const successMessage = document.createElement("div");
            successMessage.id = "successMessage";
            successMessage.style.color = "green";
            successMessage.style.fontWeight = "bold";
            successMessage.style.marginTop = "15px";
            successMessage.style.display = "none";
            successMessage.textContent = "✅ Successfully submitted!";
            form.insertAdjacentElement("afterend", successMessage);

            // ❌ Error message element
            const errorMessage = document.createElement("div");
            errorMessage.id = "errorMessage";
            errorMessage.style.color = "red";
            errorMessage.style.fontWeight = "bold";
            errorMessage.style.marginTop = "15px";
            errorMessage.style.display = "none";
            errorMessage.textContent = "❌ Something went wrong. Please check your input.";
            form.insertAdjacentElement("afterend", errorMessage);

            function validateName() {
                const name = nameInput.value.trim();
                if (name === "") {
                    nameError.textContent = "Name is required.";
                    return false;
                } else if (!/^[A-Za-z\s]+$/.test(name)) {
                    nameError.textContent = "Only letters and spaces allowed.";
                    return false;
                } else {
                    nameError.textContent = "";
                    return true;
                }
            }

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,6}$/i;
                if (email === "") {
                    emailError.textContent = "Email is required.";
                    return false;
                } else if (!emailPattern.test(email)) {
                    emailError.textContent = "Invalid email format.";
                    return false;
                } else {
                    emailError.textContent = "";
                    return true;
                }
            }

            function validateMessage() {
                const message = messageInput.value.trim();
                if (message === "") {
                    messageError.textContent = "Message cannot be empty.";
                    return false;
                } else {
                    messageError.textContent = "";
                    return true;
                }
            }

            function validateForm() {
                const isNameValid = validateName();
                const isEmailValid = validateEmail();
                const isMessageValid = validateMessage();
                submitBtn.disabled = !(isNameValid && isEmailValid && isMessageValid);
            }

            // Attach real-time validation
            nameInput.addEventListener('input', () => { validateName(); validateForm(); });
            emailInput.addEventListener('input', () => { validateEmail(); validateForm(); });
            messageInput.addEventListener('input', () => { validateMessage(); validateForm(); });

            // Submit handler
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                if (!submitBtn.disabled) {
                    const formData = new FormData(form);

                    fetch("contact.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        // If the result is "success", display the success message
                        if (result === "success") {
                            errorMessage.style.display = "none";
                            successMessage.style.display = "block";
                            form.reset();
                            submitBtn.disabled = true;

                            // Hide success message after 10 seconds
                            setTimeout(() => {
                                successMessage.style.display = "none";
                            }, 10000);
                        } else {
                            // If there's an error message, show the error message
                            successMessage.style.display = "none";
                            errorMessage.style.display = "block";
                            setTimeout(() => {
                                errorMessage.style.display = "none";
                            }, 10000);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        successMessage.style.display = "none";
                        errorMessage.style.display = "block";
                    });
                }
            });
        });
    </script>
</body>
</html>
