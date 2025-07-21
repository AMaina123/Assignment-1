<?php
// Start the session to manage user authentication state
session_start();

// Load MySQLi database connection
require "db.php"; // Should define $conn as mysqli object

// ⚙️ Handle form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve submitted credentials, or default to empty strings
  $email    = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Prepare query to fetch user details securely based on email
  $stmt = $conn->prepare("
    SELECT u.id, u.email, u.username, u.password, r.role
    FROM users u
    JOIN roles r ON u.role_id = r.roleId
    WHERE u.email = ?
  ");
  $stmt->bind_param("s", $email); // Bind email as string
  $stmt->execute();
  $result = $stmt->get_result();

  // ✅ If user found, verify password
  if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
      // 🌟 Login success — set session variables
      $_SESSION['user_id']    = $user['id'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['username']   = $user['username'];
      $_SESSION['user_role']  = $user['role'];

      // Redirect to dashboard
      header("Location: Dashboard.php");
      exit;
    } else {
      // ❌ Password incorrect
      $error = "Incorrect email or password.";
    }
  } else {
    // ❌ Email not found
    $error = "Incorrect email or password.";
  }

  // Clean up
  $stmt->close();
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

<!-- 🧭 Navigation bar -->
<div class="topnav">
  <a href="Homepage.php">Home</a>
  <a href="ContactUs.php">Contact Us</a>
  <div class="topnav-right">
    <a href="Login.php" class="active">Login</a>
    <a href="SignUp.php">Sign Up</a>
  </div>
</div>

<!-- 🏷️ Page header -->
<div class="header">
  <h1>Login to LegalGuide</h1>
</div>

<!-- 🧾 Login form section -->
<div class="row">
  <div class="main-content">
    <h2>Login</h2>

    <div class="second-content">
      <!-- Display error message if login fails -->
      <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>

      <form method="post" action="">
        <input type="email" name="email" placeholder="Enter your email" required /><br>
        <input type="password" name="password" placeholder="Enter your password" required /><br>
        <input type="submit" value="Login" />
        <p><a href="SignUp.php">Don't have an account? Sign Up</a></p>
      </form>

      <!-- 👋 Greeting sidebar -->
      <div class="sidebar">
        <h2>Welcome Back!</h2>
        <p>Legal insights, personalized dashboards, and secure consultations await you.</p>
      </div>
    </div>
  </div>
</div>

<!-- 🔻 Footer -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
</div>

</body>
</html>