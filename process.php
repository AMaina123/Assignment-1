<?php
require "db.php"; // This should create a mysqli $conn connection
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
      $_SESSION['user_id'] = $stmt->insert_id;
      $_SESSION['user_email'] = $email;

      // Grab role name (optional)
      $role_sql = $conn->prepare("SELECT role FROM roles WHERE roleId = ?");
      $role_sql->bind_param("s", $role_id);
      $role_sql->execute();
      $role_result = $role_sql->get_result();
      $role = $role_result->fetch_assoc()['role'];

      $_SESSION['user_role'] = $role;

      header("Location: Dashboard.php");
      exit;
    } else {
      $message = " Error: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>