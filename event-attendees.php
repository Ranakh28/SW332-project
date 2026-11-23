<?php
// Sprint 2 - PB-08: club leader views the registered students of an event.
// Sprint 2 - PB-09: club leader marks digital attendance (Present / Absent / Pending).
require 'helpers.php';
require_leader();
$id = (int)($_GET['id'] ?? 0);

// PB-08: only the club leader who owns the hosting club can open this page.
// This check also runs before saving attendance (PB-09), so a POST is never trusted on its own.
$q = $pdo->prepare('SELECT e.*, c.name AS club_name, c.id AS club_id
    FROM events e JOIN clubs c ON c.id = e.club_id
    WHERE e.id = ? AND c.leader_id = ?');
$q->execute([$id, current_user()['id']]);
$event = $q->fetch();
if (!$event) { http_response_code(403); exit('Access denied. Only the leader of the hosting club can view this attendee list.'); }

// PB-09: save all attendance changes at once.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $submitted = $_POST['attendance'] ?? [];
    if (!is_array($submitted) || !$submitted) {
        flash('No attendance changes were submitted.', 'error');
        redirect('event-attendees.php?id=' . $id);
    }

    // Validate every row before touching the database.
    $changes = [];
    foreach ($submitted as $registrationId => $status) {
        if (!ctype_digit((string)$registrationId) || !is_string($status) || !in_array($status, ATTENDANCE_STATUSES, true)) {
            flash('Invalid attendance data. Nothing was saved.', 'error');
            redirect('event-attendees.php?id=' . $id);
        }
        $changes[(int)$registrationId] = $status;
    }

    try {
        $pdo->beginTransaction();
        // Lock this event's registrations and make sure every submitted ID belongs to THIS event.
        $q = $pdo->prepare('SELECT id FROM registrations WHERE event_id = ? FOR UPDATE');
        $q->execute([$id]);
        $validIds = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
        $foreign = array_diff(array_keys($changes), $validIds);
        if ($foreign) {
            $pdo->rollBack();
            flash('Some students in the form are not registered for this event. Refresh the page and try again. Nothing was saved.', 'error');
            redirect('event-attendees.php?id=' . $id);
        }
        // UPDATE only (never INSERT): attendance lives on the existing registration row,
        // so a student can never get a second attendance record for the same event.
        $u = $pdo->prepare('UPDATE registrations SET attendance_status = ? WHERE id = ? AND event_id = ?');
        foreach ($changes as $registrationId => $status) {
            $u->execute([$status, $registrationId, $id]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('Could not save attendance. Please try again.', 'error');
        redirect('event-attendees.php?id=' . $id);
    }

    $saved = array_count_values($changes);
    flash('Attendance saved for ' . count($changes) . ' student' . (count($changes) === 1 ? '' : 's') . ': '
        . ($saved['Present'] ?? 0) . ' Present, ' . ($saved['Absent'] ?? 0) . ' Absent, ' . ($saved['Pending'] ?? 0) . ' Pending.');
    redirect('event-attendees.php?id=' . $id); // Post/Redirect/Get: a refresh will not resubmit the form
}

// PB-08: join registrations with users for this event (PB-09 adds attendance_status).
$q = $pdo->prepare('SELECT r.id AS registration_id, r.registered_at, r.attendance_status, u.name, u.email
    FROM registrations r JOIN users u ON u.id = r.user_id
    WHERE r.event_id = ?
    ORDER BY r.registered_at ASC');
$q->execute([$id]);
$attendees = $q->fetchAll();

$total = count($attendees);
$capacity = (int)$event['capacity'];

// PB-09 / Task 9.3: counters always come from the database, so they are correct after save and refresh.
$counts = ['Present' => 0, 'Absent' => 0, 'Pending' => 0];
foreach ($attendees as $a) {
    $status = in_array($a['attendance_status'], ATTENDANCE_STATUSES, true) ? $a['attendance_status'] : 'Pending';
    $counts[$status]++;
}
$eventStarted = strtotime($event['event_date']) <= time();

$pageTitle = 'Attendees - ' . $event['title'];
require 'header.php';
?>
<section class="page-intro">
  <span class="eyebrow">EVENT ATTENDEES · <?=e($event['club_name'])?></span>
  <h1><?=e($event['title'])?></h1>
  <p>📍 <?=e($event['venue'])?> · <?=date('l, M j, Y g:i A', strtotime($event['event_date']))?></p>
  <div class="badge-row">
    <span class="capacity-badge <?= $total >= $capacity ? 'full' : '' ?>"><?=$total?> / <?=$capacity?> Registered</span>
    <span class="status-badge present">Present: <?=$counts['Present']?></span>
    <span class="status-badge absent">Absent: <?=$counts['Absent']?></span>
    <span class="status-badge pending">Pending: <?=$counts['Pending']?></span>
  </div>
  <a class="text-link" href="manage-club.php?id=<?=$event['club_id']?>">← Back to manage club</a>
</section>

<?php if (!$attendees): ?>
  <div class="empty">No students registered for this event yet.</div>
<?php else: ?>
<?php if (!$eventStarted): ?>
  <p class="muted note">This event has not started yet. You can prepare the attendance list now and update it during the event.</p>
<?php endif; ?>
<form method="post" class="table-card attendance-form">
  <?php csrf_field(); ?>
  <div class="table-scroll">
  <table class="attendee-table">
    <thead><tr><th>#</th><th>Student name</th><th>University email</th><th>Registered at</th><th>Attendance</th></tr></thead>
    <tbody>
    <?php foreach ($attendees as $i => $a):
        $rid = (int)$a['registration_id'];
        $current = in_array($a['attendance_status'], ATTENDANCE_STATUSES, true) ? $a['attendance_status'] : 'Pending'; ?>
      <tr>
        <td class="num"><?=$i + 1?></td>
        <td data-label="Student"><strong><?=e($a['name'])?></strong></td>
        <td data-label="Email"><?=e($a['email'])?></td>
        <td data-label="Registered"><?=date('M j, Y g:i A', strtotime($a['registered_at']))?></td>
        <td data-label="Attendance">
          <div class="attendance-choice" role="radiogroup" aria-label="Attendance for <?=e($a['name'])?>">
            <?php foreach (ATTENDANCE_STATUSES as $status): ?>
              <label class="choice <?=strtolower($status)?>">
                <input type="radio" name="attendance[<?=$rid?>]" value="<?=$status?>" <?=$current === $status ? 'checked' : ''?>>
                <span><?=$status?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="form-actions"><button class="btn primary">Save Attendance</button></div>
</form>
<?php endif; ?>
<?php require 'footer.php'; ?>
