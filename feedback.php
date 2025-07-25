<?php
// 🚀 Session & DB
session_start();
require 'db.php';

// 🎯 Query all feedback
$query = "SELECT full_name, email, subject, message, submitted_at FROM feedback ORDER BY submitted_at DESC";
$result = $conn->query($query);
$feedbackList = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin | User Feedback</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- 🔗 Navigation Bar -->
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

<!-- 📌 Page Title -->
<div class="header">
  <h1>User Feedback</h1>
</div>

<!-- 📥 Container -->
<div class="container">
  <div class="main-content">
    <h2>Recent Submissions</h2>

    <!-- 📄 Report Download Button -->
    <form method="POST" action="generateFeedbackReport.php" target="_blank" style="margin-bottom: 15px;">
      <button type="submit" class="btn btn-sm btn-secondary">Download Feedback PDF Report</button>
    </form>

    <!-- 🗂️ Feedback Table -->
    <?php if (count($feedbackList) > 0): ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($feedbackList as $feedback): ?>
        <tr>
          <td><?= htmlspecialchars($feedback['full_name']) ?></td>
          <td><?= htmlspecialchars($feedback['email']) ?></td>
          <td><?= htmlspecialchars($feedback['subject']) ?></td>
          <td><?= nl2br(htmlspecialchars($feedback['message'])) ?></td>
          <td><?= date("Y-m-d H:i", strtotime($feedback['submitted_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>No feedback has been submitted yet.</p>
    <?php endif; ?>
  </div>
</div>

<!-- 📬 Footer -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>