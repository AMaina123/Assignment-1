<?php
session_start();
require "db.php";

$conn = new mysqli("localhost", "root", "Menerator.1", "legalguide2");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$escalation_message = '';
$userId = $_SESSION['user_id'] ?? null;

$location = $_POST['location'] ?? '';
$expertise = $_POST['expertise'] ?? '';
$details = $_POST['details'] ?? '';

$result = null;
$filter_query = "
  SELECT u.id AS lawyer_id, lp.full_name
  FROM lawyer_profiles lp
  JOIN users u ON lp.user_id = u.id
  WHERE lp.availability_status = 'Available'
";

$params = [];
$types  = '';

if (!empty($location)) {
    $filter_query .= " AND lp.location = ?";
    $params[] = $location;
    $types .= 's';
}

if (!empty($expertise)) {
    $filter_query .= " AND FIND_IN_SET(?, lp.expertise)";
    $params[] = $expertise;
    $types .= 's';
}

$stmt = $conn->prepare($filter_query);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lawyer_id'])) {
    if (!$userId) {
        $escalation_message = "You must be logged in to escalate a query.";
    } else {
        $lawyer_id = $_POST['lawyer_id'];
        $now = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO escalated_queries (
                user_id, lawyer_id, query_type, location, details, escalated_at
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissss", $userId, $lawyer_id, $expertise, $location, $details, $now);
        $stmt->execute();
        $stmt->close();

        $escalation_message = "Query escalated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Escalate Legal Query - LegalGuide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- 🔝 Navigation Bar -->
<div class="topnav">
  <a href="Homepage.php">Home</a>
  <a href="Dashboard.php">Dashboard</a>
  <a href="escalateQuery.php" class="active">Escalate Query</a>
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

<!-- 🏷️ Page Header -->
<div class="header">
  <h1>Escalate Legal Query</h1>
</div>

<!-- Escalation Section -->
<div class="container">
  <div class="main-content">
    <p>Select filters and submit your legal query for professional guidance.</p>

    <!-- 🔍 Filtering Form -->
    <form method="POST" action="escalateQuery.php" class="second-content">
      <label for="location">Location:</label>
      <select name="location" onchange="this.form.submit()">
        <option value="">-- Select Location --</option>
        <option value="Nairobi" <?= $location === 'Nairobi' ? 'selected' : '' ?>>Nairobi</option>
        <option value="Mombasa" <?= $location === 'Mombasa' ? 'selected' : '' ?>>Mombasa</option>
        <option value="Kisumu"  <?= $location === 'Kisumu'  ? 'selected' : '' ?>>Kisumu</option>
      </select>

      <label for="expertise">Expertise Area:</label>
      <select name="expertise" onchange="this.form.submit()">
        <option value="">-- Select Expertise --</option>
        <?php
          foreach ($expertiseAreas as $area) {
            echo "<option value=\"$area\" " . ($expertise === $area ? "selected" : "") . ">$area</option>";
          }
        ?>
      </select>
    </form>

    <!-- 📝 Escalation Form -->
    <form method="POST" action="escalateQuery.php" class="second-content">
      <?php if (!empty($escalation_message)): ?>
        <p style="color: green; text-align: center;"><?php echo $escalation_message; ?></p>
      <?php endif; ?>

      <label for="details">Query Details:</label>
      <textarea name="details" rows="4" placeholder="Describe your legal issue in detail..." required></textarea>

      <label for="lawyer">Select Advocate:</label>
      <select name="lawyer_id" required>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= $row['lawyer_id'] ?>"><?= htmlspecialchars($row['full_name']) ?></option>
          <?php endwhile; ?>
        <?php else: ?>
          <option value="">No lawyers found for selected filters</option>
        <?php endif; ?>
      </select>

      <button type="submit">Escalate Query</button>
    </form>
  </div>

  <!-- 📌 Sidebar -->
  <aside class="sidebar">
    <a href="Dashboard.php">Dashboard</a>
    <a href="MyHobbies.php">My Hobbies</a>
    <a href="AboutMe.php">About Me</a>
    <a href="ContactUs.php">Contact Us</a>
  </aside>
</div>

<!-- 📎 Footer -->
<div class="footer">
  <p>&copy; 2025 LegalGuide. All rights reserved.</p>
  <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
</div>

</body>
</html>