<?php
session_start();
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Trim inputs
  $email = trim($email);
  $password = trim($password);

  // Initialize error flag
  $error = '';

  //  Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email address.";
  }

  // Enforce strong password rules
  elseif (strlen($password) < 8 || 
          !preg_match("/[A-Z]/", $password) || 
          !preg_match("/[a-z]/", $password) || 
          !preg_match("/[0-9]/", $password)) {
    $error = "Password must be at least 8 characters and include uppercase, lowercase, and a number.";
  }

  // If no errors, proceed with authentication
  if (empty($error)) {
    $stmt = $conn->prepare("
      SELECT u.id, u.email, u.username, u.password, r.role
      FROM users u
      JOIN roles r ON u.role_id = r.roleId
      WHERE u.email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
      if (password_verify($password, $user['password'])) {
        
        // Successful login
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['user_role']  = $user['role'];

        header("Location: Dashboard.php");
        exit;
      } else {
        $error = "Incorrect email or password.";
      }
    } else {
      $error = "Incorrect email or password.";
    }
    $stmt->close();
  }
}
?>
  


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - LegalGuide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!--  Navigation bar -->
<div class="topnav">
  <a href="Homepage.php">Home</a>
  <a href="ContactUs.php">Contact Us</a>
  <div class="topnav-right">
    <a href="Login.php" class="active">Login</a>
    <a href="SignUp.php">Sign Up</a>
  </div>
</div>

<!--  Page header -->
<div class="header">
  <h1>Login to LegalGuide</h1>
</div>

<!--  Login form section -->
  <div class="container">
      <div class="main-content">
        <h2>Login</h2>
      
        <div class="second-content">
          <form method="post" action="">
          <input type="email" name="email" placeholder="Enter your email" required /><br>
          <input type="password" name="password" placeholder="Enter your password" required /><br>
          <input type="submit" value="Login" />

          <!-- Display error message if login fails -->
          <?php if (isset($error)): ?>
          <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
          <?php endif; ?>
          
          <p><a href="SignUp.php">Don't have an account? Sign Up</a></p>
          <p><a href="forgotpassword.php">Forgot your password?</a></p>
          </form>

        </div>
      </div>
      <!--  Greeting sidebar -->
      <div class="sidebar">
        <h2>Welcome Back!</h2>
        <p>Legal insights, personalized dashboards, and secure consultations await you.</p>
      </div>
    </div>

    
  </div>
   
<!--  Footer -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>