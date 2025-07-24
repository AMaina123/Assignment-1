<!--  Submit Legal Query -->
<section class="second-content">
  <h2>Submit a Legal Query</h2> <br>
  <form method="post" action="">
    <textarea name="query_text" rows="6" placeholder="Describe your legal issue clearly..." required></textarea>
    <br><input type="submit" value="Submit Query" />
  </form>

  <?php if (!empty($query_message)): ?>
    <p class="feedback error"><?php echo htmlspecialchars($query_message); ?></p>
  <?php endif; ?>

  <?php if (!empty($query_response)): ?>
    <div class="response-box">
      <h3>Response</h3>
      <p><?php echo htmlspecialchars($query_response); ?></p>
      <form method="post" action="escalateQuery.php">
        <input type="hidden" name="query_id" value="<?php echo $query_id; ?>" />
        <button type="submit">Escalate Query</button>
      </form>
    </div>
  <?php endif; ?>
</section>

<!--  Past Legal Queries -->
<section class="second-content">
  <h2>Your Previous Queries</h2>
  <?php if (!empty($past_queries)): ?>
    <ul class="query-list">
      <?php foreach ($past_queries as $q): ?>
        <li>
          <strong>Date:</strong> <?php echo date("M d, Y", strtotime($q['submitted_at'])); ?><br>
          <strong>Query:</strong> <em><?php echo htmlspecialchars($q['query_text']); ?></em><br>
          <?php if (!empty($q['response'])): ?>
            <strong>Response:</strong>
            <p><?php echo htmlspecialchars($q['response']); ?></p>
          <?php else: ?>
            <strong>Status:</strong> Pending response<br>
            <form method="post" action="escalateQuery.php" class="inline-form">
              <input type="hidden" name="query_id" value="<?php echo $q['id']; ?>" />
              <button type="submit">Escalate</button>
            </form>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <br>
    <p>No legal queries submitted yet.</p>
  <?php endif; ?>
</section>

<!-- Consultation Access -->
<section class="second-content">
  <h2>Book a Legal Consultation</h2> <br>
  <p>Choose LSK-verified lawyers by location and expertise.</p>
  <a href="bookconsultation.php" class="btn">Get Started</a>
</section>

<!--  Scheduled Consultations -->
<section class="second-content">
  <h2>Your Scheduled Consultations</h2> <br>
  <?php if (!empty($user_appts)): ?>
  <table class="consultation-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Purpose</th>
        <th>Lawyer</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($user_appts as $appt): ?>
        <tr>
          <td><?= date("M d, Y", strtotime($appt['appointment_date'])) ?></td>
          <td><?= date("H:i", strtotime($appt['appointment_time'])) ?></td>
          <td><?= htmlspecialchars($appt['purpose']) ?></td>
          <td><?= htmlspecialchars($appt['lawyer_name']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No upcoming consultations booked.</p>
<?php endif; ?>
</section>

<!--  User Tips -->
<section class="second-content">
  <h3>Tips for Using LegalGuide</h3>
  <ul>
    <li> Submit legal queries anytime</li>
    <li> Receive guidance from verified lawyers</li>
    <li> View past queries and responses</li>
    <li> Escalate unresolved issues for expert attention</li>
    <li> Book consultations tailored to your needs</li>
  </ul>
</section>