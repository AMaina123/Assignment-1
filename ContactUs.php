<?php
session_start();
require 'db.php'; // ✅ Ensure the DB connection is established

// 🔐 Handle logout requests
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
  session_destroy();
  header("Location: Login.php");
  exit;
}

// 📨 Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $conn->real_escape_string($_POST['full_name']);
  $email = $conn->real_escape_string($_POST['email']);
  $subject = $conn->real_escape_string($_POST['subject']);
  $message = $conn->real_escape_string($_POST['message']);
  $newsletter = $conn->real_escape_string($_POST['subscribe']);

  $insert = "
    INSERT INTO feedback (full_name, email, subject, message)
    VALUES ('$name', '$email', '$subject', '$message')
  ";

  if ($conn->query($insert)) {
    $feedbackMsg = "<div class='success'>Thanks for your feedback, $name!</div>";
  } else {
    $feedbackMsg = "<div class='error'>Sorry, something went wrong. Try again later.</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Contact Us</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- 🌐 Navigation -->
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

<!-- 🏷️ Header -->
<div class="header">
  <h1>Contact Us</h1>
</div>

<!-- 🧾 Main Contact Section -->
<div class="container">
  <div class="main-content">
    <p>
      <label for="contact">Want to chat? Reach out below: </label><br>
    </p>

    <div class="second-content">
      <img src="Images/envelope.png" width="100" height="100" alt="envelope icon"> 
      Email: weirdeskproduction@wrddesk.com<br>
      <img src="Images/phone.png" width="100" height="100" alt="phone icon"> 
      Phone: 0293023487<br>
    </div>

    <!-- 📤 Feedback Form -->
    <div class="second-content">
      <?php if (isset($feedbackMsg)) echo $feedbackMsg; ?>
      <form method="POST" action="ContactUs.php" class="feedback-form">
        <label for="full_name">Full Name:</label><br>
        <input type="text" name="full_name" required><br>

        <label for="email">Email Address:</label><br>
        <input type="email" name="email" required><br>

        <label for="subject">Subject:</label><br>
        <input type="text" name="subject"><br>

        <label for="message">Message:</label><br>
        <textarea name="message" rows="5" placeholder="Enter your comments" required></textarea><br>

        <!-- 📰 Newsletter Subscription -->
        <label>Newsletter Subscription:</label><br>
        <input type="radio" name="subscribe" value="yes" required /> Yeah, why not?<br>
        <input type="radio" name="subscribe" value="no" /> Na, no thanks.<br><br>

        <button type="submit">Submit</button>
      </form>
    </div>
  </div>
</div>

<!-- 📬 Footer -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>