<?php
require 'db.php';
session_start();

//  Fetch counts from the database
$userCount = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$queryCount = $conn->query("SELECT COUNT(*) AS count FROM queries")->fetch_assoc()['count'];
$apptCount = $conn->query("SELECT COUNT(*) AS count FROM appointments")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin | System Analytics</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!--  Top Navigation -->
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
    <h1>System Logs & Analytics</h1>
  </div>

  <div class="container">
    <!--  Sidebar -->
    <div class="sidebar">
      <a href="Dashboard.php">Dashboard</a><br>
      <a href="Profile.php">My Profile</a><br>
      <a href="ContactUs.php">Contact Us</a><br>
    </div>

    <!--  Analytics Table -->
    <div class="main-content">
      <div class="card">
        <h2>Platform Usage Summary</h2>

        <!--  Report Button -->
        <form action="generateReport.php" method="post" target="_blank" style="margin-bottom: 15px;">
          <button type="submit" class="btn btn-sm btn-secondary">Download PDF Report</button>
        </form>

        <!--  Data Table -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Metric</th>
              <th>Count</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Total Users</td><td><?= $userCount ?></td></tr>
            <tr><td>Legal Queries</td><td><?= $queryCount ?></td></tr>
            <tr><td>Consultations</td><td><?= $apptCount ?></td></tr>
          </tbody>
        </table>
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