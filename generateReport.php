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
    $query = "SELECT specialization, COUNT(*) AS count FROM lawyers GROUP BY specialization";
    $result = $conn->query($query);
    $html .= "<h3>Lawyer Breakdown by Specialization</h3><table border='1' cellpadding='5'><tr><th>Specialization</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
      $html .= "<tr><td>{$row['specialization']}</td><td>{$row['count']}</td></tr>";
    }
    $html .= "</table>";
    break;

  case 'user_feedback':
    $query = "SELECT sentiment, COUNT(*) AS count FROM feedback GROUP BY sentiment";
    $result = $conn->query($query);
    $html .= "<h3>User Feedback Overview</h3><table border='1' cellpadding='5'><tr><th>Sentiment</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
      $html .= "<tr><td>{$row['sentiment']}</td><td>{$row['count']}</td></tr>";
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