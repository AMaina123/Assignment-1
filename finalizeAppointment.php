<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointment_id'])) {
  $id = intval($_POST['appointment_id']);
  $stmt = $conn->prepare("UPDATE appointments SET status = 'finalized' WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: Dashboard.php");
exit;