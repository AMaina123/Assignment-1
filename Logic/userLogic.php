<!-- 👤 User: Submit Legal Query -->
<div class="second-content">
  <h2>Submit a Legal Query</h2>
  <form method="post" action="">
    <textarea name="query_text" rows="8" cols="60" placeholder="Describe your legal issue clearly..." required></textarea><br>
    <input type="submit" value="Submit Query" />
  </form>

  <?php if (!empty($query_message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($query_message); ?></p>
  <?php endif; ?>

  <?php if (!empty($query_response)): ?>
    <div class="response-box">
      <h3>Response:</h3>
      <p><?php echo htmlspecialchars($query_response); ?></p>
      <form method="post" action="escalateQuery.php">
        <input type="hidden" name="query_id" value="<?php echo $query_id; ?>">
        <button type="submit">Escalate Query</button>
      </form>
    </div>
  <?php endif; ?>
</div>

<!-- 🕘 User: Past Legal Queries -->
<div class="second-content">
  <h2>Past Legal Queries</h2>
  <?php if (!empty($past_queries)): ?>
    <ul class="query-list">
      <?php foreach ($past_queries as $q): ?>
        <li style="margin-bottom: 20px;">
          <strong>Date:</strong> <?php echo date("M d, Y", strtotime($q['submitted_at'])); ?><br>
          <strong>Your Query:</strong> <em><?php echo htmlspecialchars($q['query_text']); ?></em><br>
          <?php if (!empty($q['response'])): ?>
            <strong>Response:</strong> <p><?php echo htmlspecialchars($q['response']); ?></p>
          <?php else: ?>
            <strong>Status:</strong> Pending response<br>
            <form method="post" action="escalateQuery.php">
              <input type="hidden" name="query_id" value="<?php echo $q['id']; ?>">
              <button type="submit">Escalate Query</button>
            </form>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No legal queries submitted yet.</p>
  <?php endif; ?>
</div>

<!-- 📅 Book a Consultation -->
<div class="second-content">
  <h2>Book a Consultation</h2>
  <?php if (isset($appt_message)): ?>
    <p style="color: green;"><?php echo htmlspecialchars($appt_message); ?></p>
  <?php endif; ?>

  <form method="post" action="">
    <label>Date & Time:</label><br>
    <input type="datetime-local" name="appointment_date" required><br><br>

    <label>Purpose:</label><br>
    <textarea name="purpose" rows="4" cols="50" required></textarea><br><br>

    <label>Select Lawyer:</label><br>
    <select name="lawyer_id" required>
      <option value="">Choose</option>
      <?php
      $lawyerQuery = $conn->query("SELECT id, full_name FROM users WHERE role_id = (SELECT roleId FROM roles WHERE role = 'lawyer')");
      while ($l = $lawyerQuery->fetch_assoc()) {
        echo "<option value='{$l['id']}'>{$l['full_name']}</option>";
      }
      ?>
    </select><br><br>
    <input type="submit" value="Book Consultation" />
  </form>
</div>

<!-- 📆 View Scheduled Consultations -->
<div class="second-content">
  <h2>Your Scheduled Consultations</h2>
  <?php if (!empty($myAppointments)): ?>
    <table border="1" cellpadding="6">
      <tr><th>Date</th><th>Lawyer</th><th>Purpose</th><th>Status</th></tr>
      <?php foreach ($myAppointments as $appt): ?>
        <tr>
          <td><?php echo date("M d, Y H:i", strtotime($appt['appointment_date'])); ?></td>
          <td><?php echo htmlspecialchars($appt['full_name']); ?></td>
          <td><?php echo htmlspecialchars($appt['purpose']); ?></td>
          <td><?php echo htmlspecialchars($appt['status']); ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No upcoming consultations booked.</p>
  <?php endif; ?>
</div>

<!-- 💡 Tips -->
<div class="role-section">
  <h3>User Tips</h3>
  <p>You can submit queries, view responses, book consultations, and escalate unresolved issues. LegalGuide is here to assist you.</p>
</div>