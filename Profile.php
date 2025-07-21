<?php
// Start session to ensure user is logged in
session_start();
require "db.php"; // Your MySQLi connection via $conn

//  Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit;
}

$userId = $_SESSION['user_id']; // Get current user ID from session

//  Fetch user details using JOIN between users and roles
$user_stmt = $conn->prepare("
  SELECT u.full_name, u.email, u.phone, r.role
  FROM users u
  JOIN roles r ON u.role_id = r.roleId
  WHERE u.id = ?
");
$user_stmt->bind_param("i", $userId); // Bind integer user ID
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc(); // Get one row: user profile
$user_stmt->close();

// 📚 Fetch user's submitted legal queries
$query_stmt = $conn->prepare("
  SELECT query_text, response, submitted_at
  FROM queries
  WHERE user_id = ?
  ORDER BY submitted_at DESC
");
$query_stmt->bind_param("i", $userId);
$query_stmt->execute();
$queries_result = $query_stmt->get_result();

// Create an array to hold each query for display
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
    <a href="SignUp.php">Sign Up</a>
    <a href="Logout.php">Logout</a>
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
      <div class="main-content">
        <form method="post" action="updateProfile.php">
          <label for="name">Your Name:</label><br>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br>

          <label for="email">Email:</label><br>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly><br>

          <label for="phone">Phone:</label><br>
          <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"><br>

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
  </div>

  <!--  Sidebar links -->
  <aside class="sidebar">
    <a href="MyProject.php">My Project</a>
    <a href="MyHobbies.php">My Hobbies</a>
    <a href="AboutMe.php">About Me</a>
    <a href="ContactUs.php">Contact Us</a>
  </aside>
</div>

<!--  Footer branding -->
<div class="footer">
  <img src="Images/lawyer.png" width="200" height="300" alt="cartoon lawyer" />
</div>

</body>
</html>