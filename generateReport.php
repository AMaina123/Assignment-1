<?php
ob_start(); // Prevent accidental output that breaks PDF headers
require('tcpdf/tcpdf.php');
require 'db.php';

$reportType = $_GET['report_type'] ?? 'user_management';

$html = "<h2>LegalGuide Report: " . ucfirst(str_replace('_', ' ', $reportType)) . "</h2>";
$html .= "<p><strong>Generated on:</strong> " . date("Y-m-d H:i") . "</p>";

switch ($reportType) {

  case 'user_management':
    $query = "SELECT role_id, COUNT(*) AS count FROM users WHERE is_deleted = 0 GROUP BY role_id";
    $result = $conn->query($query);
    $roles = [1 => 'Admin', 2 => 'Lawyer', 3 => 'User'];
    $html .= "<h3>User Role Breakdown</h3><table border='1' cellpadding='5'><tr><th>Role</th><th>Count</th></tr>";
    $total = 0;
    while ($row = $result->fetch_assoc()) {
      $role = $roles[$row['role_id']] ?? 'Unknown';
      $count = $row['count'];
      $html .= "<tr><td>$role</td><td>$count</td></tr>";
      $total += $count;
    }
    $html .= "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td></tr></table>";
    break;

  case 'lawyer_management':
  $query = "
    SELECT 
      u.id, u.full_name, u.email, u.created_at AS user_created_at,
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
      u.created_at DESC
  ";

  $result = $conn->query($query);
  $html .= "<h3>Registered Lawyers</h3><table border='1' cellpadding='5'>
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
      <th>Account Created</th>
    </tr>";

  while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
      <td>" . htmlspecialchars($row['full_name']) . "</td>
      <td>" . htmlspecialchars($row['email']) . "</td>
      <td>" . htmlspecialchars($row['experience']) . "</td>
      <td>" . htmlspecialchars($row['location']) . "</td>
      <td>" . htmlspecialchars(ucfirst($row['availability_status'])) . "</td>
      <td>" . nl2br(htmlspecialchars($row['expertise'])) . "</td>
      <td>" . nl2br(htmlspecialchars($row['bio'])) . "</td>
      <td>" . htmlspecialchars($row['certificate_number']) . "</td>
      <td>" . htmlspecialchars($row['document_type']) . "</td>
      <td>" . date("Y-m-d", strtotime($row['uploaded_at'])) . "</td>
      <td>" . $row['total_consultations'] . "</td>
      <td>" . date("Y-m-d", strtotime($row['user_created_at'])) . "</td>
    </tr>";
  }

  $html .= "</table>";
  break;

 case 'feedback':
  $query = "SELECT full_name, email, subject, message, submitted_at FROM feedback ORDER BY submitted_at DESC";
  $result = $conn->query($query);

  $html .= "<h3>User Feedback Submissions</h3>";
  $html .= "<table border='1' cellpadding='5'>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Subject</th>
      <th>Message</th>
      <th>Submitted</th>
    </tr>";

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $html .= "<tr>
        <td>" . htmlspecialchars($row['full_name']) . "</td>
        <td>" . htmlspecialchars($row['email']) . "</td>
        <td>" . htmlspecialchars($row['subject']) . "</td>
        <td>" . nl2br(htmlspecialchars($row['message'])) . "</td>
        <td>" . date("Y-m-d H:i", strtotime($row['submitted_at'])) . "</td>
      </tr>";
    }
  } else {
    $html .= "<tr><td colspan='5'>No feedback submissions found.</td></tr>";
  }

  $html .= "</table>";
  break;
  
  case 'system_analytics':
    // Mirror dashboard logic: total users, legal queries, consultations
    $userCount = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
    $queryCount = $conn->query("SELECT COUNT(*) AS count FROM queries")->fetch_assoc()['count'];
    $apptCount = $conn->query("SELECT COUNT(*) AS count FROM appointments")->fetch_assoc()['count'];

    $html .= "<h3>Platform Usage Summary</h3><table border='1' cellpadding='5'>
      <tr><th>Metric</th><th>Count</th></tr>
      <tr><td>Total Users</td><td>{$userCount}</td></tr>
      <tr><td>Legal Queries</td><td>{$queryCount}</td></tr>
      <tr><td>Consultations</td><td>{$apptCount}</td></tr>
    </table>";
    break;

  default:
    $html .= "<p><em>No report configuration found for type: $reportType</em></p>";
    break;
}

ob_end_clean(); // Clear any output before PDF generation

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("LegalGuide_{$reportType}_Report.pdf", 'I');
?>