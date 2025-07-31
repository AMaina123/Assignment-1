<?php
session_start();
require 'db.php';

//  Handle logout
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: Homepage.php");
  exit;
}

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'updateRole') {
  $userId = $_POST['user_id'];
  $newRole = $_POST['new_role'];

  $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
  $stmt->bind_param("ii", $newRole, $userId);
  $stmt->execute();
}

// Soft delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'deleteUser') {
  $userId = $_POST['user_id'];

  $stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
}

// Role filtering logic
$selectedRole = isset($_GET['role_filter']) ? intval($_GET['role_filter']) : 0;

if ($selectedRole > 0) {
  $stmt = $conn->prepare("SELECT * FROM users WHERE role_id = ? AND is_deleted = 0 ORDER BY created_at DESC");
  $stmt->bind_param("i", $selectedRole);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM users WHERE is_deleted = 0 ORDER BY created_at DESC");
}

$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin | User Management</title>
  <link rel="stylesheet" href="css/style.css"> <!-- Use your site's stylesheet -->
</head>
<body>

  <!--  Top Navigation Bar -->
  <div class="topnav">
    <a href="Homepage.php"> Home </a>
    <a href="Dashboard.php"> Dashboard</a>
    <a href="ContactUs.php"> Contact Us</a>
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

  <!--  Page Header -->
  <div class="header">
    <h1>Admin Panel — Manage Users</h1>
  </div>

  <div class="container">
    <!--  Sidebar -->
    <div class="sidebar">
      <a href="Dashboard.php"> Dashboard</a><br>
      <a href="Profile.php"> My Profile</a><br>
      <a href="ContactUs.php"> Contact Us</a><br>
    </div>

    <!--  Main Content -->
    <div class="main-content">
      <div class="card">
        <h2>User Management</h2>

        <!--  Filter by Role -->
        <form method="get" action="manageUsers.php" class="role-filter-form">
          <label for="role_filter"><strong>Filter by Role:</strong></label>
          <select name="role_filter" onchange="this.form.submit()" class="form-control" style="width:auto;">
            <option value="0" <?= $selectedRole === 0 ? 'selected' : '' ?>>All Roles</option>
            <option value="1" <?= $selectedRole === 1 ? 'selected' : '' ?>>Admin</option>
            <option value="2" <?= $selectedRole === 2 ? 'selected' : '' ?>>Lawyer</option>
            <option value="3" <?= $selectedRole === 3 ? 'selected' : '' ?>>User</option>
          </select>
        </form>

        <!-- 👥 Table of Users -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['full_name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= $user['role_id'] == 1 ? 'Admin' : ($user['role_id'] == 2 ? 'Lawyer' : 'User') ?></td>
              <td>
                <div class="action-buttons">
                  <!-- Update Role -->
                  <form method="post" action="manageUsers.php" class="inline-form">
                    <input type="hidden" name="action" value="updateRole">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="new_role" class="form-control">
                      <option value="1" <?= $user['role_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                      <option value="2" <?= $user['role_id'] == 2 ? 'selected' : '' ?>>Lawyer</option>
                      <option value="3" <?= $user['role_id'] == 3 ? 'selected' : '' ?>>User</option>
                    </select>
                    <button type="submit" class="btn btn-sm">Update</button>
                  </form>

                  <!-- Delete User (Soft Delete) -->
                  <form method="post" action="manageUsers.php" onsubmit="return confirm('Remove this user from the system?')" class="inline-form">
                    <input type="hidden" name="action" value="deleteUser">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <form method="post" action="generateReport.php" target="_blank">
            <button type="submit" class="btn btn-sm btn-primary">Download Role Summary PDF</button>
        </form>
      </div>
    </div>
  </div>

  <!--  Footer -->
  <div class="footer">
    <p>&copy; 2025 LegalGuide. All rights reserved.</p>
    <p>Need help? <a href="mailto:support@legalguide.com">support@legalguide.com</a></p>
  </div>

</body>
</html>