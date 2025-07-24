<!-- Manage Appointments -->
<?php if (!empty($appointments)): ?>
  <div class="role-section">
    <h3>Manage Appointments</h3>
    <table border="1" cellpadding="8">
      <tr>
        <th>Client</th>
        <th>Date</th>
        <th>Purpose</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php foreach ($appointments as $appt): ?>
        <tr>
          <td><?php echo htmlspecialchars($appt['full_name']); ?></td>
          <td><?php echo date("M d, Y H:i", strtotime($appt['appointment_date'])); ?></td>
          <td><?php echo htmlspecialchars($appt['purpose']); ?></td>
          <td><?php echo htmlspecialchars($appt['status']); ?></td>
          <td>
            <?php if ($appt['status'] === 'Pending'): ?>
              <form method="post" action="finalizeAppointment.php" style="display:inline;">
                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                <button type="submit">Finalize</button>
              </form>
            <?php else: ?>
              <span style="color: green;"> Finalized</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<!-- Escalated Legal Queries -->
<?php 

if (!empty($escalated_queries)): ?>
  <div class="role-section">
    <h3>Escalated Legal Queries</h3>
    <table border="1" cellpadding="8">
      <tr><th>Date</th><th>Client</th><th>Query</th><th>Action</th></tr>
      <?php foreach ($escalated_queries as $q): ?>
        <tr>
          <td><?php echo date("M d, Y", strtotime($q['submitted_at'])); ?></td>
          <td><?php echo htmlspecialchars($q['full_name']); ?></td>
          <td><?php echo htmlspecialchars($q['query_text']); ?></td>
          <td>
            <form method="post" action="replyQuery.php">
              <input type="hidden" name="query_id" value="<?php echo $q['id']; ?>">
              <button type="submit">Respond</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<p style="color: orange;">Appointments count: <?php echo count($appointments); ?></p>
<p style="color: orange;">Escalated queries count: <?php echo count($escalated_queries); ?></p>