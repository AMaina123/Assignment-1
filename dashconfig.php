<?php
require "db.php";

// 🔐 Redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit;
}

// 🔓 Logout functionality
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
  session_destroy();
  header("Location: Login.php");
  exit;
}

// 👤 Session Details
$username = $_SESSION['username'] ?? 'User';
$role     = strtolower($_SESSION['user_role'] ?? 'user'); // 'user', 'lawyer', 'admin'

// 🧭 Initialize
$query_message  = '';
$query_response = '';
$query_id       = null;
$past_queries   = [];
$appointments   = [];
$escalated_queries = [];
$appt_message   = '';

// ------------------------------------------------------------
// 🧠 Handle Legal Query Submission
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query_text'])) {
  $query_text = trim($_POST['query_text']);

  if (empty($query_text)) {
    $query_message = "⚠️ Please enter a legal question.";
  } else {
    // Check for duplicate query
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM queries WHERE user_id = ? AND query_text = ?");
    $check_stmt->bind_param("is", $_SESSION['user_id'], $query_text);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
      $query_message = "You've already submitted that question.";
    } else {
      // Insert query
      $stmt = $conn->prepare("INSERT INTO queries (user_id, query_text) VALUES (?, ?)");
      $stmt->bind_param("is", $_SESSION['user_id'], $query_text);

      if ($stmt->execute()) {
        $query_id = $stmt->insert_id;

        // 🧠 Simulated AI Response
        $keywords = [
          'land' => 'Kenyan land law covers title deeds, leasehold/freehold, and succession rights.',
          'tenant' => 'Tenancy rights fall under the Landlord and Tenant Act.',
          'contract' => 'Contracts must meet legal validity under the Law of Contract Act.',
          'divorce' => 'Divorce involves both statutory and customary law.',
          'employment' => 'Workers are protected under the Employment Act.',
          'inheritance' => 'Guided by the Law of Succession Act.',
          'harassment' => 'Protection orders or criminal action may apply.',
          'traffic' => 'Traffic offenses are penalized under the Traffic Act.',
          'assault' => 'Assault is a Penal Code offense.',
          'contractor' => 'Contract disputes may require small claims litigation.',
          'child support' => 'Enforceable under Kenyan family law.',
          'debt' => 'Handled through civil litigation or negotiation.',
          'cybercrime' => 'Covered by the Computer Misuse and Cybercrimes Act.',
          'fraud' => 'May be pursued both civilly and criminally.',
          'burglary' => 'Report under the Penal Code.',
          'defamation' => 'Covers libel and slander in civil court.',
          'passport' => 'Seek Immigration Department intervention.',
          'accident' => 'Gather evidence for insurance or court claims.',
          'license' => 'Often under administrative law provisions.',
          'nuisance' => 'Pursue claims impacting property enjoyment.'
        ];

        $simulated_response = "We’re reviewing your query. A lawyer will respond shortly.";
        foreach ($keywords as $keyword => $reply) {
          if (stripos($query_text, $keyword) !== false) {
            $simulated_response = $reply;
            break;
          }
        }

        // Save response
        $save = $conn->prepare("UPDATE queries SET response = ? WHERE id = ?");
        $save->bind_param("si", $simulated_response, $query_id);
        $save->execute();
        $save->close();

        $query_response = $simulated_response;
        $query_message  = "Your query has been submitted successfully.";
      } else {
        $query_message = "Error: " . $stmt->error;
      }
      $stmt->close();
    }
  }
}

// ------------------------------------------------------------
// 📅 Retrieve Upcoming Appointments for User
// ------------------------------------------------------------
$user_appts = [];
$appt_stmt = $conn->prepare("
  SELECT a.appointment_date, a.appointment_time, a.purpose, u.full_name AS lawyer_name
  FROM appointments a
  JOIN users u ON a.lawyer_id = u.id
  WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
  ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$appt_stmt->bind_param("i", $_SESSION['user_id']);
$appt_stmt->execute();
$user_appts_result = $appt_stmt->get_result();
while ($row = $user_appts_result->fetch_assoc()) {
  $user_appts[] = $row;
}
$appt_stmt->close();

// ------------------------------------------------------------
// 🕘 Booking Form Submission (User/Admin)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_date'])) {
  $date      = $_POST['appointment_date'] ?? '';
  $time      = $_POST['appointment_time'] ?? '';
  $purpose   = $_POST['purpose'] ?? '';
  $lawyer_id = $_POST['lawyer_id'] ?? '';

  if ($date && $time && $purpose && $lawyer_id) {
    $now = date('Y-m-d H:i:s');

    $insert_appt = $conn->prepare("
      INSERT INTO appointments (user_id, lawyer_id, appointment_date, appointment_time, purpose, status, created_at)
      VALUES (?, ?, ?, ?, ?, 'Pending', ?)
    ");
    $insert_appt->bind_param("iissss", $_SESSION['user_id'], $lawyer_id, $date, $time, $purpose, $now);

    if ($insert_appt->execute()) {
      $appt_message = "Consultation requested successfully.";
    } else {
      $appt_message = "Appointment error: " . $insert_appt->error;
    }
    $insert_appt->close();
  }
}

// ------------------------------------------------------------
// 📥 Retrieve Past Queries
// ------------------------------------------------------------
$query_stmt = $conn->prepare("
  SELECT id, query_text, response, submitted_at
  FROM queries
  WHERE user_id = ?
  ORDER BY submitted_at DESC
  LIMIT 5
");
$query_stmt->bind_param("i", $_SESSION['user_id']);
$query_stmt->execute();
$query_result = $query_stmt->get_result();
while ($row = $query_result->fetch_assoc()) {
  $past_queries[] = $row;
}
$query_stmt->close();

// ------------------------------------------------------------
// 🔍 Load Consultations Assigned to Logged-in Lawyer
// ------------------------------------------------------------
if ($role === 'lawyer') {
  $lawyer_appts = $conn->prepare("
    SELECT a.id, u.full_name, a.appointment_date, a.appointment_time, a.purpose, a.status
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.lawyer_id = ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
  ");
  $lawyer_appts->bind_param("i", $_SESSION['user_id']);
  $lawyer_appts->execute();
  $view_result = $lawyer_appts->get_result();
  while ($row = $view_result->fetch_assoc()) {
    $appointments[] = $row;
  }
  $lawyer_appts->close();
}

// ------------------------------------------------------------
// 🧭 Load Escalated Queries (Lawyer Only)
// ------------------------------------------------------------
if ($role === 'lawyer') {
  $eq_stmt = $conn->prepare("
    SELECT q.id, q.query_text, u.full_name, q.submitted_at
    FROM queries q
    JOIN users u ON q.user_id = u.id
    WHERE q.status = 'escalated'
    ORDER BY q.submitted_at DESC
  ");
  $eq_stmt->execute();
  $eq_result = $eq_stmt->get_result();
  while ($row = $eq_result->fetch_assoc()) {
    $escalated_queries[] = $row;
  }
  $eq_stmt->close();
}
?>