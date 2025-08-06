<?php
// --- CONFIG & INITIALIZATION ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = new mysqli("localhost", "root", "Menerator.1", "legalguide2");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

session_start(); // Include session for nav bar logic

$feedback_message = '';
$feedback_type = '';

// --- STEP 1: Handle Email Submission for Reset Link ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback_message = "Invalid email format.";
        $feedback_type = "error";
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();
        $stmt->close();

        $reset_link = "http://localhost/forgotpassword.php?token=$token";

        $subject = "LegalGuide Password Reset";
        $message = "Click this link to reset your password:\n$reset_link";
        $headers = "From: legalguide@example.com";

        if (mail($email, $subject, $message, $headers)) {
            $feedback_message = "Reset link sent to: " . htmlspecialchars($email);
            $feedback_type = "success";
        } else {
            $feedback_message = "Failed to send email.";
            $feedback_type = "error";
        }
    }
}

// --- STEP 2: Handle Token and Display Password Form ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (strtotime($row['expires_at']) > time()) {
            $show_password_form = true;
        } else {
            $feedback_message = "Token expired. Please request a new one.";
            $feedback_type = "error";
        }
    } else {
        $feedback_message = "Invalid token.";
        $feedback_type = "error";
    }
}

// --- STEP 3: Handle Password Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['new_password'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 1) {
        $email = $result->fetch_assoc()['email'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        $feedback_message = "Password updated successfully! You can now log in.";
        $feedback_type = "success";
    } else {
        $feedback_message = "Reset failed. Invalid or expired token.";
        $feedback_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LegalGuide | Forgot Password</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .form-card {
        align-items: center;
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 500px;
        margin: auto;
    }
    .feedback { padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
    input, button { width: 100%; padding: 10px; margin-top: 10px; }
  </style>
</head>
<body>

  <!--  Navigation Bar -->
  <div class="topnav">
    <a href="Homepage.php">Home</a>
    <a href="Dashboard.php">Dashboard</a>
    <a href="ContactUs.php">Contact Us</a>
    <div class="topnav-right">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="Profile.php">My Profile</a>
        <a href="Homepage.php?logout=true" style="color: #dc3545;">Logout</a>
      <?php else: ?>
        <a href="SignUp.php">Sign Up</a>
        <a href="Login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>

  <!--  Page Header -->
  <div class="header">
    <h1>Reset Your Password</h1>
  </div>

  <div class="container">
    <!--  Sidebar -->
    <div class="sidebar">
      <a href="Dashboard.php">Dashboard</a><br>
      <a href="Profile.php">My Profile</a><br>
      <a href="ContactUs.php">Contact Us</a><br>
    </div>

    <!--  Main Content -->
    <div class="main-content" style="padding: 20px;">
      <div class="form-card">
        <h2>Enter your email</h2>

        <?php if (!empty($feedback_message)): ?>
          <div class="feedback <?= $feedback_type ?>">
            <?= htmlspecialchars($feedback_message) ?>
          </div>
        <?php endif; ?>

        <?php if (isset($show_password_form) && $show_password_form): ?>
          <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Reset Password</button>
          </form>
        <?php else: ?>
          <form method="POST" action="">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p>&copy; 2025 LegalGuide. All rights reserved.</p>
    <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
  </div>
</body>
</html>