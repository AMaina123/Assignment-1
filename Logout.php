<?php
session_start();
session_destroy(); // 🔓 Clears all session data
header("Location: Login.php"); // 🚪 Redirect to login
exit;
?>