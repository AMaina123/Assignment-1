<?php
require "db.php";

$comments = $_POST['comments'] ?? '';
$subscribe = $_POST['subscribe'] ?? 'no';
$userId = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("INSERT INTO contacts (user_id, comment, subscribe, submitted_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$userId, $comments, $subscribe]);

header("Location: ContactUs.php?submitted=true");
exit;
?>