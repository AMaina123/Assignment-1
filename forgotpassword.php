<?php
// --- CONFIG & INITIALIZATION ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
$conn = new mysqli("localhost", "root", "Menerator.1", "legalguide2");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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

        // TEMP MAIL FUNCTION: Replace with PHPMailer later
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
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f5f5f5; }
        .form-container { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        .feedback { padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        input, button { width: 100%; padding: 10px; margin-top: 10px; }
        a.btn { display: block; padding: 10px; text-align: center; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Password</h2>

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
</body>
</html>     