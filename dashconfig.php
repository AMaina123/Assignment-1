<?php

// Redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
  header("Location: Login.php");
  exit;
}

// Extract session data
$username = $_SESSION['username'] ?? 'User';
$role     = $_SESSION['user_role'] ?? 'user'; // Roles: 'user', 'lawyer', 'admin'
$role = strtolower($_SESSION['user_role'] ?? 'user');

// 🛠 Initialize variables
$query_message  = '';       // Message for errors or notices
$query_response = '';       // Generated AI response
$query_id       = null;     // ID of last inserted query
$past_queries   = [];       // User query history
$appointments   = [];       // Lawyer appointment list

// ------------------------------------------------------------
// Handle Legal Query Submission and AI Response
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query_text'])) {
  $query_text = trim($_POST['query_text']);

  if (empty($query_text)) {
    $query_message = "⚠️ Please enter a legal question.";
  } else {
    // Check if user already submitted same query
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM queries WHERE user_id = ? AND query_text = ?");
    $check_stmt->bind_param("is", $_SESSION['user_id'], $query_text);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
      $query_message = "You've already submitted that question.";
    } else {
      // Insert query into database
      $stmt = $conn->prepare("INSERT INTO queries (user_id, query_text) VALUES (?, ?)");
      $stmt->bind_param("is", $_SESSION['user_id'], $query_text);
      if ($stmt->execute()) {
        $query_id = $stmt->insert_id;

        // Simulate AI response locally using keyword-based logic
        $keywords = [
          'land' => 'It looks like your query involves land matters. Kenyan land law includes considerations like title deeds, leasehold vs. freehold, and succession rights.',
          'tenant' => 'Your query involves tenancy. Under Kenyan law, tenant rights are governed by the Landlord and Tenant Act and may require formal notice for eviction.',
          'contract' => 'This appears to relate to contracts. The Law of Contract Act sets out requirements for validity, breach, and enforcement.',
          'divorce' => 'Divorce proceedings in Kenya involve both customary and statutory law. The Matrimonial Causes Act covers key processes such as custody and division of assets.',
          'employment' => 'Employment law in Kenya protects workers under the Employment Act. Issues such as unfair dismissal, contracts, and working conditions apply here.',
          'inheritance' => 'Succession and inheritance issues in Kenya are guided by the Law of Succession Act',
          'harassment' => 'Harassment may be criminal or civil in nature, depending on severity. Consider filing under the Penal Code or seeking protection orders.',
          'traffic' => 'Traffic violations in Kenya involve fines, license points, and possible court appearances under the Traffic Act.',
          'assault' => 'Assault is a criminal offense under the Penal Code. You may lodge a complaint at the nearest police station.',
          'contractor' => 'Disputes with contractors often involve breach of service contracts. Review terms and possible small claims actions.',
          'child support' => 'Child maintenance is enforceable under Kenyan family law. Court orders can help secure regular support.',
          'debt' => 'Debt recovery may be handled via informal negotiations or formal litigation through civil court.',
          'cybercrime' => 'Cyber offenses are governed by the Computer Misuse and Cybercrimes Act, including hacking, fraud, and data breaches.',
          'fraud' => 'Fraud is both a criminal and civil issue. Depending on scale, it may warrant police reporting or litigation.',
          'burglary' => 'Burglary and theft are offenses under the Penal Code. Report to police and consider legal restitution options.',
          'defamation' => 'Defamation cases hinge on reputation harm. Kenyan law recognizes libel and slander under civil litigation.',
          'passport' => 'Issues with passports may require intervention from the Immigration Department or legal challenge if rights are violated.',
          'accident' => 'Accidents may involve insurance claims, compensation, and liability law. Consider gathering witness and police reports.',
          'license' => 'Licensing disputes may relate to business operations, driving, or land use — often under administrative law.',
          'nuisance' => 'Nuisance claims can be pursued when disruptions affect personal enjoyment of property or peace.'
         ];
        
        $simulated_response = "We’re reviewing your query. A lawyer will respond shortly.";
        
        foreach ($keywords as $keyword => $reply) {
          if (stripos($query_text, $keyword) !== false) {
            $simulated_response = $reply;
            break;
          }
        };
        
        // Save simulated reply into database
        $save = $conn->prepare("UPDATE queries SET response = ? WHERE id = ?");
        $save->bind_param("si", $simulated_response, $query_id);
        $save->execute();
        $save->close();

$query_response = $simulated_response;
        $query_message = "Your query has been submitted successfully.";
      } else {
        $query_message = "Error: " . $stmt->error;
      }
      $stmt->close();
    }
  }
}

      

// ------------------------------------------------------------
// Retrieve User's Past Queries
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
$result = $query_stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $past_queries[] = $row;
}
$query_stmt->close();

// ------------------------------------------------------------
//  Handle Consultation Booking (User/Admin)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_date'])) {
  $date       = $_POST['appointment_date'] ?? '';
  $purpose    = $_POST['purpose'] ?? '';
  $lawyer_id  = $_POST['lawyer_id'] ?? '';

  if ($date && $purpose && $lawyer_id) {
    $appt_stmt = $conn->prepare("
      INSERT INTO appointments (user_id, lawyer_id, appointment_date, purpose, status)
      VALUES (?, ?, ?, ?, 'Pending')
    ");
    $appt_stmt->bind_param("iiss", $_SESSION['user_id'], $lawyer_id, $date, $purpose);
    if ($appt_stmt->execute()) {
      $appt_message = "Consultation requested successfully.";
    } else {
      $appt_message = "Appointment error: " . $appt_stmt->error;
    }
    $appt_stmt->close();
  }
}

// ------------------------------------------------------------
// Load Appointments Assigned to Logged-in Lawyer
// ------------------------------------------------------------
if ($role === 'lawyer') {
  $appt_view = $conn->prepare("
    SELECT a.id, u.full_name, a.appointment_date, a.purpose, a.status
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.lawyer_id = ?
    ORDER BY a.appointment_date ASC
  ");
  $appt_view->bind_param("i", $_SESSION['user_id']);
  $appt_view->execute();
  $view_result = $appt_view->get_result();
  while ($row = $view_result->fetch_assoc()) {
    $appointments[] = $row;
  }
  $appt_view->close();
}

// ------------------------------------------------------------
// Load Escalated Queries for Lawyer Role
// ------------------------------------------------------------
$escalated_queries = [];

if ($role === 'lawyer') {
  $eq_stmt = $conn->prepare("
    SELECT q.id, q.query_text, u.full_name, q.submitted_at
    FROM queries q
    JOIN users u ON q.user_id = u.id
    WHERE q.status = 'escalated'
    ORDER BY q.submitted_at DESC
  ");
  $eq_stmt->execute();
  $result = $eq_stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $escalated_queries[] = $row;
  }
  $eq_stmt->close();
}



?>