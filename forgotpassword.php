<?php
// Enable error reporting for debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection (replace with your actual credentials)
$conn = new mysqli("localhost", "root", "Menerator.1", "legalguide2");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// === STEP 1: Process Password Reset Request via Email ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>Invalid email format.</p>";
    } else {
        // Generate token and expiry time
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert token into password_resets table
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();
        $stmt->close();

        // Compose the reset link
        $reset_link = "http://localhost/forgotpassword.php?token=$token";

        // Send email with PHP's mail function (use PHPMailer for production)
        $subject = "LegalGuide Password Reset";
        $message = "Click this link to reset your password:\n$reset_link";
        $headers = "From: legalguide@example.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "<p style='color:green;'>Reset link sent to: $email</p>";
        } else {
            echo "<p style='color:red;'>Failed to send email.</p>";
        }
    }
}

// === STEP 2: Show Password Reset Form ===
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token from database
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // Token found and not expired?
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (strtotime($row['expires_at']) > time()) {
            // Show password input form
            echo '<form method="POST" action="">
                    <input type="hidden" name="token" value="' . htmlspecialchars($token) . '">
                    <input type="password" name="new_password" placeholder="Enter new password" required>
                    <button type="submit">Reset Password</button>
                  </form>';
        } else {
            echo "<p style='color:red;'>Token expired. Please request a new one.</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid token.</p>";
    }
}

// === STEP 3: Process Password Update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['new_password'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    // Check token again before proceeding
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in users table
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        $stmt->close();

        // Delete the reset token
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();

        echo "<p style='color:green;'>Password updated successfully! You can now log in.</p>";
    } else {
        echo "<p style='color:red;'>Reset failed. Invalid token.</p>";
    }
}
?>

<!-- === STEP 4: Basic Email Submission Form === -->
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 30px; background-color: #f5f5f5; }
    .form-container { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
    input[type="email"], input[type="password"], button {
      width: 100%; padding: 10px; margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Forgot Password</h2>
    <form method="POST" action="">
      <input type="email" name="email" placeholder="Enter your email" required>
      <button type="submit">Send Reset Link</button>
    </form>
  </div>
</body>
</html>