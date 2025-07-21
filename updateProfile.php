<?php
session_start();
require "db.php";

$userId = $_SESSION['user_id'] ?? null;
$name   = $_POST['name'] ?? '';
$phone  = $_POST['phone'] ?? '';

if ($userId && $name) {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $userId]);
    header("Location: Profile.php");
}
?>