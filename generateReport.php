<?php
require_once('tcpdf/tcpdf.php');
include 'db.php';

//  Fetch role counts
$query = "
  SELECT role_id, COUNT(*) AS count 
  FROM users 
  WHERE is_deleted = 0 
  GROUP BY role_id
";
$result = $conn->query($query);

$roleNames = [1 => 'Admin', 2 => 'Lawyer', 3 => 'User'];
$reportContent = "<h2>LegalGuide User Role Report</h2>";
$reportContent .= "<p><strong>Generated on:</strong> " . date("Y-m-d H:i") . "</p>";
$reportContent .= "<table border='1' cellpadding='5'><tr><th>Role</th><th>Count</th></tr>";
$total = 0;

while ($row = $result->fetch_assoc()) {
  $roleName = $roleNames[$row['role_id']] ?? 'Unknown';
  $count = $row['count'];
  $reportContent .= "<tr><td>$roleName</td><td>$count</td></tr>";
  $total += $count;
}
$reportContent .= "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td></tr>";
$reportContent .= "</table>";

// 📄 Init TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->writeHTML($reportContent, true, false, true, false, '');
$pdf->Output('User_Role_Report.pdf', 'I'); // 'I' = view in browser
?>