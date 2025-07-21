<?php
// 🧭 Start session and include config/logic
session_start();
require "db.php";            // MySQLi connection
require "config.php";        // API keys & constants
require "dashconfig.php";    // Universal dashboard logic
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <!-- 🔗 Navigation Bar -->
  <div class="topnav">
    <a href="Homepage.php">Home</a>
    <a href="Dashboard.php">Dashboard</a>
    <a href="Profile.php">My Profile</a>
    <div class="topnav-right">
      <a href="SignUp.php">Sign Up</a>
      <a href="Login.php">Login</a>
    </div>
  </div> 

  <!-- 🧑‍💼 Personalized Header -->
  <div class="header">
    <h1>Welcome To Your Dashboard, <?php echo htmlspecialchars($username); ?></h1>
  </div>

  <!-- 🧩 Main Content Container -->
  <div class="container">
    <div class="main-content">
      <h2>Your Activity</h2>

      <p style="color: yellow;">Detected role: <strong><?php echo $role; ?></strong></p>

      <!-- 👤 Render Section Based on Role -->
      <?php
        if ($role === 'user') {
          include 'Logic/userLogic.php';
        } elseif ($role === 'lawyer') {
          include 'Logic/lawyerLogic.php';
        } elseif ($role === 'admin') {
          include 'Logic/adminLogic.php';
        } else {
          echo "<p>🔒 Unrecognized role. Please contact support.</p>";
        }
      ?>
    </div>
  </div>

  <!-- 📄 Footer -->
  <div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

</body>
</html>