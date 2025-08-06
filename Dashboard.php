<?php
//  Start session and include config/logic
session_start();
require "db.php";            // MySQLi connection
require "config.php";        // API keys & constants if any
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

  <!--  Navigation Bar -->
  <div class="topnav a">
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

  <!-- Personalized Header -->
  <div class="header">
    <h1>Welcome To Your Dashboard, <?php echo htmlspecialchars($username); ?></h1>
  </div>

  <!--  Main Content Container -->
  <div class="container">
    <div class="main-content">
      <h2><u>Your Activity</u></h2>

      <!-- Render Section Based on Role -->
      <?php
        if ($role === 'user') {
          include 'Logic/userLogic.php';
        } elseif ($role === 'lawyer') {
          include 'Logic/lawyerLogic.php';
        } elseif ($role === 'admin') {
          include 'Logic/adminLogic.php';
        } else {
          echo "<p> Unrecognized role. Please contact support.</p>";
        }
      ?>
    </div>
  </div>

  <!--  Footer -->
  <div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

</body>
</html>