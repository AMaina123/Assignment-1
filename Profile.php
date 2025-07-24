
<?php
// Session Control & Authentication
session_start();
require "db.php"; //  Initializes MySQLi connection via $conn

//  Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit;
}

//  Logout Trigger (via GET parameter)
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
  session_destroy();
  header("Location: Login.php");
  exit;
}

$userId = $_SESSION['user_id']; // 👤 Get current user ID from session

// ----------------------------------------------------
//  1. Fetch Logged-In User’s Profile Info
// ----------------------------------------------------
$user_stmt = $conn->prepare("
  SELECT u.full_name, u.email, u.phone, r.role
  FROM users u
  JOIN roles r ON u.role_id = r.roleId
  WHERE u.id = ?
");
$user_stmt->bind_param("i", $userId);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc(); //  Pulls user profile record
$user_stmt->close();


// ----------------------------------------------------
//  2. Update Profile (Full Name & Phone via POST)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedName  = trim($_POST['name'] ?? '');
$updatedPhone = trim($_POST['phone'] ?? '');

//  Only update if both name and session ID are present
if ($userId && $updatedName) {
  if (
    !preg_match('/^07[0-9]{8}$/', $updatedPhone) && 
    !preg_match('/^\+2547[0-9]{8}$/', $updatedPhone)
    ) {
      $_SESSION['profile_message'] = "Please enter a valid Kenyan phone number: either 07XXXXXXXX or +2547XXXXXXXX.";
    } else {
      $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
      $update_stmt->bind_param("ssi", $updatedName, $updatedPhone, $userId);
      $update_stmt->execute();
      $update_stmt->close();
       $_SESSION['profile_message'] = "Profile updated successfully!";
}

  //  Redirect after profile update
  header("Location: Profile.php");
  exit;
}
} 

// ----------------------------------------------------
//  3. Fetch User's Submitted Legal Queries
// ----------------------------------------------------
$query_stmt = $conn->prepare("
  SELECT query_text, response, submitted_at
  FROM queries
  WHERE user_id = ?
  ORDER BY submitted_at DESC
");
$query_stmt->bind_param("i", $userId);
$query_stmt->execute();
$queries_result = $query_stmt->get_result();

//  Store all queries into an array for display
$queries = [];
while ($row = $queries_result->fetch_assoc()) {
  $queries[] = $row;
}
$query_stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Profile - LegalGuide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!--  Navigation bar -->
<div class="topnav">
  <a href="Homepage.php">Home</a>  
  <a href="Dashboard.php">Dashboard</a>  
  <a href="Profile.php" class="active">My Profile</a>  
  <a href="ContactUs.php">Contact Us</a>
  <div class="topnav-right">
    <a href="?logout=true" style="color: #dc3545; font-weight: bold;">Logout</a>
  </div>
</div>

<!--  Page title -->
<div class="header">
  <h1>Manage Your Profile</h1>
</div>

<div class="container">
  <div class="main-content">
    <p>Update your personal information or review your past legal queries.</p>
    
    <!--  User Profile Form -->
    <div class="row">
    <div class="second-content">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <label for="name">Your Name:</label><br>
          <input type="text" id="name" name="name" autocomplete="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br>

          <label for="email">Email:</label><br>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly><br>

          <label for="phone">Phone:</label><br>
          <input type="tel" id="phone" name="phone" autocomplete="name" value="<?php echo htmlspecialchars($user['phone']); ?>"><br>
          <?php if (!empty($_SESSION['profile_message'])): ?>
          <p style="color: <?php echo (strpos($_SESSION['profile_message'], 'success') !== false) ? 'green' : 'red'; ?>;">
          <?php echo htmlspecialchars($_SESSION['profile_message']); ?>
           <?php unset($_SESSION['profile_message']); ?>
           </p>
          <?php endif; ?>

          <label for="role">Your Role:</label><br>
          <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" readonly><br>

          <input type="submit" value="Update Profile" />
          <input type="reset" value="Reset" />
        </form>
      </div>
    </div>

    <!--  User's Query History -->
      <div class="second-content">
        <div class="query-history">
          <h2>Your Previous Queries</h2>
          <?php if (!empty($queries)): ?>
            <ul>
            <?php foreach ($queries as $q): ?>
              <li>
              <strong><?php echo date("M d, Y", strtotime($q['submitted_at'])); ?>:</strong><br>
              <em>Q:</em> <?php echo htmlspecialchars($q['query_text']); ?><br>
              <em>A:</em> <?php echo htmlspecialchars($q['response']); ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No queries submitted yet.</p>
      <?php endif; ?>
    </div>
  </div>
    
  <div class="second-content">
    <h2>Previously Attended Consultations</h2>  
    <?php if (!empty($past_appts)): ?>
    <ul class="appointment-list">
      <?php foreach ($past_appts as $appt): ?>
        <li>
          <strong>Date:</strong> <?= date("M d, Y", strtotime($appt['appointment_date'])) ?><br>
          <strong>Time:</strong> <?= date("H:i", strtotime($appt['appointment_time'])) ?><br>
          <strong>Purpose:</strong> <?= htmlspecialchars($appt['purpose']) ?><br>
          <strong>Lawyer:</strong> <?= htmlspecialchars($appt['lawyer_name']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No consultations attended yet.</p>
  <?php endif; ?>
  </div>

  <div class="second-content">
    <h2>Delete My Account <h2>
   <form method="POST" action="deleteAccount.php" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
  <button type="submit" style="color: #fff; background-color: #dc3545; border: none; padding: 0.5em 1em;">
    Delete My Account
  </button>
</form>
  </div>
  
  </div>

 

  <!--  Sidebar links -->
  <aside class="sidebar">
    <a href="Dashboard.php">Dashboard</a>
    <a href="MyHobbies.php">My Hobbies</a>
    <a href="AboutMe.php">About Me</a>
    <a href="ContactUs.php">Contact Us</a>
  </aside>
</div>

<!--  Footer branding -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

</body>
</html>