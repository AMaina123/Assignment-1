<?php
session_start();
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
  <a href="Profile.php">My Profile</a> 
  <div class="topnav-right">
    <a href="SignUp.php">Sign Up</a>
    <a href="Login.php">Login</a>
  </div>
</div>

<div class="header">
  <h1>Contact Us</h1>
</div>

<div class="container">
  <div class="main-content">
    <p>
      <label for="contact">Want to chat? Reach out below: </label><br>
      <img src="Images/envelope.png" width="100" height="100" alt="envelope icon"> Email: weirdeskproduction@wrddesk.com<br>
      <img src="Images/phone.png" width="100" height="100" alt="phone icon"> Phone: 0293023487<br>
    </p>

    <form action="submitContact.php" method="post">
      <label for="comments">Got any comments? Put them here:</label><br>
      <textarea name="comments" id="comments" placeholder="Enter your comments" required></textarea><br>

      <label>Newsletter Subscription:</label><br>
      <input type="radio" name="subscribe" value="yes" required /> Yeah, why not?<br>
      <input type="radio" name="subscribe" value="no" /> Na, no thanks.<br><br>

      <button type="submit">Submit</button>
    </form>
  </div>
</div>

<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>