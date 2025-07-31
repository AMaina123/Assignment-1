<?php
session_start();
require "db.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Delete user-related data first
$conn->query("DELETE FROM queries WHERE user_id = $user_id");
$conn->query("DELETE FROM appointments WHERE user_id = $user_id");

// Delete user from users table
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Log user out and redirect
session_destroy();
header("Location: Goodbye.php"); // Optional farewell page
exit;
?>