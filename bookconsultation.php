<?php
// -------------------------------
//  🧠 Session & Database Setup
// -------------------------------
session_start();
require "db.php";

// 🔌 Connect to database
$conn = new mysqli("localhost", "root", "Menerator.1", "legalguide2");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$booking_message = '';
$userId = $_SESSION['user_id'] ?? null;

// -------------------------------
// 🗂 Handle Consultation Booking Form
// -------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lawyer_id'])) {
    $date      = $_POST['date'] ?? '';
    $time      = $_POST['time'] ?? '';
    $purpose   = $_POST['purpose'] ?? '';
    $lawyer_id = $_POST['lawyer_id'];
    $now       = date('Y-m-d H:i:s'); // full datetime string

    if (!$userId) {
        $booking_message = "You must be logged in to book a consultation.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO appointments (
              user_id, appointment_date, appointment_time, purpose, lawyer_id, created_at
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssis", $userId, $date, $time, $purpose, $lawyer_id, $now);
        $stmt->execute();
        $stmt->close();

        $booking_message = "Consultation booked successfully!";
    }
}

// -------------------------------
// 🧭 Handle Filtering
// -------------------------------
$location  = $_POST['location'] ?? '';
$expertise = $_POST['expertise'] ?? '';

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
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Book Consultation - LegalGuide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<!-- 🔝 Navigation Bar -->
<div class="topnav">
  <a href="Homepage.php">Home</a>
  <a href="Dashboard.php">Dashboard</a>
  <a href="bookconsultation.php" class="active">Book Consultation</a>
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
  <h1>Book a Legal Consultation</h1>
</div>

<!-- Booking Section -->
<div class="container">
  <div class="main-content">
    <p>Select filters and request an appointment with a qualified advocate.</p>

    <!-- 🔍 Filtering Form -->
    <form method="POST" action="bookconsultation.php" class="second-content">
      <label for="location">Location:</label>
      <select name="location" onchange="this.form.submit()">
        <option value="">-- Select Location --</option>
        <option value="Nairobi"  <?= $location === 'Nairobi' ? 'selected' : '' ?>>Nairobi</option>
        <option value="Mombasa"  <?= $location === 'Mombasa' ? 'selected' : '' ?>>Mombasa</option>
        <option value="Kisumu"   <?= $location === 'Kisumu'  ? 'selected' : '' ?>>Kisumu</option>
      </select>

      <label for="expertise">Expertise Area:</label>
      <select name="expertise" onchange="this.form.submit()">
        <option value="">-- Select Expertise --</option>
        <?php
          $expertiseAreas = [
            "Criminal Law", "Civil Litigation", "Family Law", "Land and Property Law", "Constitutional Law",
            "Employment and Labour Law", "Corporate and Commercial Law", "Tax Law", "Immigration Law",
            "Environmental Law", "Intellectual Property Law", "Banking and Finance Law", "Insurance Law",
            "Administrative Law", "Personal Injury Law", "Contract Law", "Probate and Succession Law",
            "Consumer Protection Law", "Human Rights Law", "Matrimonial Law", "Children’s Rights Law",
            "Cyber and Technology Law", "Media and Entertainment Law", "Sports Law", "Maritime and Admiralty Law",
            "Aviation Law", "International Law", "Public Interest Law", "Education Law", "Health and Medical Law",
            "Alternative Dispute Resolution (ADR)", "Bankruptcy and Insolvency Law", "Construction Law",
            "Mining and Energy Law", "Agricultural Law", "Procurement and Tender Law",
            "Anti-Corruption and Ethics Law", "Wildlife and Natural Resources Law", "Election and Electoral Law",
            "Refugee and Asylum Law"
          ];

          foreach ($expertiseAreas as $area) {
            echo "<option value=\"$area\" " . ($expertise === $area ? "selected" : "") . ">$area</option>";
          }
        ?>
      </select>
    </form>

    <!-- 📝 Consultation Form -->
    <form method="POST" action="bookconsultation.php" class="second-content">
      <?php if (!empty($booking_message)): ?>
        <p style="color: green; text-align: center;"><?php echo $booking_message; ?></p>
      <?php endif; ?>

      <label for="purpose">Purpose:</label>
      <textarea name="purpose" rows="4" placeholder="Describe your legal issue..." required></textarea>

      <label for="date">Date:</label>
      <input type="date" name="date" required />

      <label for="time">Time:</label>
      <input type="time" name="time" required />

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

      <button type="submit">Book Consultation</button>
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