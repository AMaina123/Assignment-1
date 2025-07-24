<?php
session_start();

//Logout features
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
  session_destroy();
  header("Location: Login.php");
  exit;
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

<div class="header">
  <h1>Contact Us</h1>
</div>

<div class="container">
  <div class="main-content">
    <p>
      <label for="contact">Want to chat? Reach out below: </label><br>

      <div class="second-content">
      <img src="Images/envelope.png" width="100" height="100" alt="envelope icon"> Email: weirdeskproduction@wrddesk.com<br>
      <img src="Images/phone.png" width="100" height="100" alt="phone icon"> Phone: 0293023487<br>
    </p>
            </div>


     <div class="second-content">
    <form action="submitContact.php" method="post">
      <label for="comments">Got any comments? Put them here:</label><br>
      <textarea name="comments" id="comments" placeholder="Enter your comments" required></textarea><br>
            </div>

       <div class="second-content">
      <label>Newsletter Subscription:</label><br>
      <input type="radio" name="subscribe" value="yes" required /> Yeah, why not?<br>
      <input type="radio" name="subscribe" value="no" /> Na, no thanks.<br>
      <br>

      <button type="submit">Submit</button>
            </div>
    </form>
  </div>
</div>

<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>