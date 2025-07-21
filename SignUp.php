<?php
require "db.php"; // Initializes MySQLi $conn
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name  = $_POST['full_name'] ?? '';
  $email      = $_POST['email'] ?? '';
  $phone      = $_POST['phone'] ?? '';
  $username   = $_POST['username'] ?? '';
  $password   = $_POST['password'] ?? '';
  $confirm    = $_POST['confirm_password'] ?? '';
  $role_id    = $_POST['role_id'] ?? '';
  $gender_id  = $_POST['gender_id'] ?? '';

  if (
    empty($full_name) || empty($email) || empty($phone) || empty($username) ||
    empty($password) || empty($confirm) || empty($role_id) || empty($gender_id)
  ) {
    $message = "Please fill in all fields.";
  } elseif ($password !== $confirm) {
    $message = "Passwords do not match.";
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, username, password, role_id, gender_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $username, $hashed, $role_id, $gender_id);

    if ($stmt->execute()) {
      $_SESSION['user_id']    = $stmt->insert_id;
      $_SESSION['user_email'] = $email;
      $_SESSION['username'] = $username;

      $role_sql = $conn->prepare("SELECT role FROM roles WHERE roleId = ?");
      $role_sql->bind_param("s", $role_id);
      $role_sql->execute();
      $role_result = $role_sql->get_result();
      $role = $role_result->fetch_assoc()['role'];

      $_SESSION['user_role'] = $role;

      header("Location: Dashboard.php");
      exit;
    } else {
      $message = " Error saving account: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up - LegalGuide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<div class="topnav">
  <a href="Homepage.php">Home</a>
  <a href="ContactUs.php">Contact Us</a>
  <div class="topnav-right">
    <a href="Login.php">Sign In</a>
    <a href="SignUp.php" class="active">Sign Up</a>
  </div>
</div>

<div class="header">
  <h1>Create Your LegalGuide Account</h1>
</div>

<div class="row">
  <div class="main-content">
    <h2>Sign Up</h2>

    <?php if (isset($message)): ?>
      <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <div class="second-content">
      <form method="post" action="">
        <label for="full_name">Full Name:</label><br>
        <input type="text" name="full_name" id="full_name" required placeholder="Enter your full name" /><br>

        <label for="email">Email Address:</label><br>
        <input type="email" name="email" id="email" required placeholder="Enter your email address" /><br>

        <label for="phone">Phone Number:</label><br>
        <input type="tel" name="phone" id="phone" required placeholder="Enter your phone number" /><br>

        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required placeholder="Choose a username" /><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required placeholder="Enter your password" /><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Re-enter your password" /><br>

        <label for="role_id">Select Your Role:</label><br>
        <select name="role_id" id="role_id" required>
          <option value="">Select your role</option>
          <?php
          $role_query = "SELECT roleId, role FROM roles";
          $role_result = $conn->query($role_query);
          while ($row = $role_result->fetch_assoc()) {
            if ($row['role'] === 'Admin') continue;
            echo "<option value='" . $row['roleId'] . "'>" . $row['role'] . "</option>";
          }
          ?>
        </select><br>

        <label for="gender_id">Select Your Gender:</label><br>
        <select name="gender_id" id="gender_id" required>
          <option value="">Select your gender</option>
          <?php
          $gender_query = "SELECT genderId, gender FROM gender";
          $gender_result = $conn->query($gender_query);
          while ($row = $gender_result->fetch_assoc()) {
            echo "<option value='" . $row['genderId'] . "'>" . $row['gender'] . "</option>";
          }
          ?>
        </select><br>

        <input type="submit" value="Sign Up" style="display: block; margin-top: 20px;" />
        <p><a href="Login.php">Already have an account? Login</a></p>
      </form>
    </div>
  </div>

  <aside class="sidebar">
    <h2>LegalGuide Vision</h2>
    <p>We're committed to making legal assistance accessible, secure, and reliable for everyone. Join us.</p>
  </aside>
</div>

<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

</body>
</html>