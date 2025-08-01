<?php
// 🔌 Database Connection & Session Start
require 'db.php';
session_start();

// 📦 Comprehensive Lawyer Dataset Query
$query = "
  SELECT 
    u.id, u.full_name, u.email, u.created_at AS user_created_at, -- ✅ Pulling created_at from users
    lp.bio, lp.experience, lp.location, lp.expertise, lp.availability_status,
    ld.certificate_number, ld.document_type, ld.uploaded_at,
    COUNT(a.id) AS total_consultations
  FROM 
    users u
JOIN 
    lawyer_profiles lp ON u.id = lp.user_id
JOIN 
    lawyer_documents ld ON u.id = ld.user_id
LEFT JOIN 
    appointments a ON u.id = a.lawyer_id
WHERE 
    u.role_id = 2 AND u.is_deleted = 0
GROUP BY 
    u.id, lp.id, ld.id
ORDER BY 
    u.created_at DESC -- ✅ Sort by actual account creation time
";

$result = $conn->query($query);
$lawyers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- 🔖 Meta & Styles -->
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin | Lawyer Management</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- 🔗 Navigation Bar -->
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

  <!-- 📌 Page Header -->
  <div class="header">
    <h1>Lawyer Management</h1>
  </div>

  <div class="container">
    <!-- 📁 Sidebar -->
    <div class="sidebar">
      <a href="Dashboard.php">Dashboard</a><br>
      <a href="Profile.php">My Profile</a><br>
      <a href="ContactUs.php">Contact Us</a><br>
    </div>

    <!-- 📋 Main Lawyer Info -->
    <div class="main-content">
      <div class="card">
        <h2>Registered Lawyers & Profiles</h2>

        <!--  Report Download Button -->
        <form action="generateReport.php?report_type=lawyer_management" method="post" target="_blank" style="margin-bottom: 15px;">
          <button type="submit" class="btn btn-sm btn-secondary">Download PDF Report</button>
        </form>

        <!--  Lawyer Table -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Experience</th>
              <th>Location</th>
              <th>Availability</th>
              <th>Expertise</th>
              <th>Bio</th>
              <th>Cert #</th>
              <th>Doc Type</th>
              <th>Doc Upload</th>
              <th>Consultations</th>
              <th>Account Created</th> <!-- New Column Added -->
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lawyers as $lawyer): ?>
            <tr>
              <!-- Lawyer Details -->
              <td><?= htmlspecialchars($lawyer['full_name']) ?></td>
              <td><?= htmlspecialchars($lawyer['email']) ?></td>
              <td><?= htmlspecialchars($lawyer['experience']) ?></td>
              <td><?= htmlspecialchars($lawyer['location']) ?></td>
              <td><?= htmlspecialchars(ucfirst($lawyer['availability_status'])) ?></td>
              <td><?= nl2br(htmlspecialchars($lawyer['expertise'])) ?></td>
              <td><?= nl2br(htmlspecialchars($lawyer['bio'])) ?></td>

              <!--  Document Details -->
              <td><?= htmlspecialchars($lawyer['certificate_number']) ?></td>
              <td><?= htmlspecialchars($lawyer['document_type']) ?></td>
              <td><?= date("Y-m-d", strtotime($lawyer['uploaded_at'])) ?></td>

              <!--  Consultations Count -->
              <td><?= $lawyer['total_consultations'] ?></td>

              <!--  Account Creation Date -->
              <td><?= date("Y-m-d", strtotime($lawyer['user_created_at'])) ?></td> <!--  Displaying user.created_at -->
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>

  <!--  Footer Section -->
  <div class="footer">
    <p>&copy; 2025 LegalGuide. All rights reserved.</p>
    <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
  </div>
</body>
</html>