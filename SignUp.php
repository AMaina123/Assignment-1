<?php
require "db.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // --- Capture form inputs. ucwords auto uppercases the first string character, strtolower makes a string lowercase ---
  $full_name   = ucwords(strtolower($_POST['full_name']?? ''));
  $email       = ucwords(strtolower($_POST['email'] ?? ''));
  $phone       = ucwords(strotolower($_POST['phone'] ?? ''));
  $username    = ucwords(strotolower($_POST['username'] ?? ''));
  $password    = ucwords(strotolower($_POST['password'] ?? ''));
  $confirm     = ucwords(strotolower($_POST['confirm_password'] ?? ''));
  $role_id     = ucwords(strotolower($_POST['role_id'] ?? ''));
  $gender_id   = ucwords(strotolower($_POST['gender_id'] ?? ''));
  $certificate = ucwords(strotolower($_POST['certificate'] ?? ''));
  $expertise   = ucwords(strotolower($_POST['expertise'] ?? ''));
  $location    = ucwords(strotolower($_POST['location'] ?? ''));
  $message     = '';

  // --- Validations ---
  if (
    empty($full_name) || empty($email) || empty($phone) || empty($username) ||
    empty($password) || empty($confirm) || empty($role_id) || empty($gender_id)
  ) {
    $message = "Please fill in all fields.";
  } elseif ($password !== $confirm) {
    $message = "Passwords do not match.";
  } elseif (
    strlen($password) < 8 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password)
  ) {
    $message = "Password must be at least 8 characters and include uppercase, lowercase, and a number.";
  } elseif (
    !preg_match('/^07[0-9]{8}$/', $phone) &&
    !preg_match('/^\+2547[0-9]{8}$/', $phone)
  ) {
    $message = "Please enter a valid Kenyan phone number: either 07XXXXXXXX or +2547XXXXXXXX.";
  } else {
    // --- Check if email already exists ---
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $email_check->bind_param("s", $email);
    $email_check->execute();
    $email_result = $email_check->get_result();
    $email_check->close();

    if ($email_result->num_rows > 0) {
      $message = "An account with that email already exists. Login instead or use a different email.";
    }
  }

  // --- Fetch role name (only if not blocked yet) ---
  if (empty($message)) {
    $role_sql = $conn->prepare("SELECT role FROM roles WHERE roleId = ?");
    $role_sql->bind_param("s", $role_id);
    $role_sql->execute();
    $role_result = $role_sql->get_result();
    $role_row = $role_result->fetch_assoc();
    $role_name = $role_row['role'] ?? '';
    $role_sql->close();

    if ($role_name === 'Lawyer' && (empty($certificate) || empty($expertise) || empty($location))) {
      $message = "Lawyers must provide a certificate number, area of expertise, and location.";
    }
  }

  if (!empty($message) && strpos($message, "success") !== false) {
  // Do something
}
  // --- Proceed with insertion ---
  if (empty($message)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
      INSERT INTO users (full_name, email, phone, username, password, role_id, gender_id)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $username, $hashed, $role_id, $gender_id);

    if ($stmt->execute()) {
      $_SESSION['user_id']    = $stmt->insert_id;
      $_SESSION['user_email'] = $email;
      $_SESSION['username']   = $username;
      $_SESSION['user_role']  = $role_name;

      // --- Save lawyer-specific data ---
      if ($role_name === 'Lawyer') {
        $doc_stmt = $conn->prepare("
          INSERT INTO lawyer_documents (user_id, document_type, certificate_number, full_name, uploaded_at)
          VALUES (?, 'lawyer_certificate', ?, ?, NOW())
        ");
        $doc_stmt->bind_param("iss", $_SESSION['user_id'], $certificate, $full_name);
        $doc_stmt->execute();
        $doc_stmt->close();

        $profile_stmt = $conn->prepare("
          INSERT INTO lawyer_profiles (user_id, expertise, location)
          VALUES (?, ?, ?)
        ");
        $profile_stmt->bind_param("iss", $_SESSION['user_id'], $expertise, $location);
        $profile_stmt->execute();
        $profile_stmt->close();
      }

      // --- Redirect to dashboard ---
      header("Location: Dashboard.php");
      exit;
    } else {
      $message = "Error saving account: " . $stmt->error;
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
    <a href="Login.php">Login</a>
    <a href="SignUp.php" class="active">Sign Up</a>
  </div>
</div>

<div class="header">
  <h1>Create Your LegalGuide Account</h1>
</div>

<div class="container">
  <div class="main-content">
    <h2>Sign Up</h2>

    
    <div class="second-content">
      <form method="post" action="">
        <label for="full_name">Full Name:</label><br>
        <input type="text" name="full_name" id="full_name" required placeholder="Enter your full name" /><br>

        <label for="email">Email Address:</label><br>
        <input type="email" name="email" id="email" required placeholder="Enter your email address" /><br>
       <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

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

        <div id="lawyer-fields" style="display: none;">
          <label for="certificate">Certificate Number:</label><br>
          <input type="text" name="certificate" id="certificate" placeholder="Enter LSK certificate number"><br>
          <label for="expertise">Area of Expertise:</label><br>
           <select name="expertise" id="expertise">
             <option value="">Select your expertise</option>
                <option value="Criminal Law">Criminal Law</option>
                <option value="Civil Litigation">Civil Litigation</option>
                <option value="Family Law">Family Law</option>
                <option value="Corporate Law">Corporate Law</option>
    <!-- Add more as needed -->
            </select><br>

          <label for="location">Location:</label><br>
            <select name="location" id="location">
              <option value="">Select your location</option>
              <option value="Nairobi">Nairobi</option>
              <option value="Mombasa">Mombasa</option>
              <option value="Kisumu">Kisumu</option>
          </select><br>
</div>

        <input type="submit" value="Sign Up" style="display: block; margin-top: 20px;" />
        <p><a href="Login.php">Already have an account? Login</a></p>
      </form>
    </div>
  </div>
     <div class="sidebar">
    <h2>LegalGuide Vision</h2>
    <p>We're committed to making legal assistance accessible, secure, and reliable for everyone. Join us.</p>
  </div>

 
</div>

<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

<script>
document.getElementById('role_id').addEventListener('change', function () {
  const selectedText = this.options[this.selectedIndex].text;
  const lawyerFields = document.getElementById('lawyer-fields');
  lawyerFields.style.display = (selectedText === 'Lawyer') ? 'block' : 'none';
});
</script>
</body>
</html>