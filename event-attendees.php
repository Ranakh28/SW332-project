<?php
// Sprint 2 - PB-08: club leader views the registered students of an event.
require 'helpers.php';
require_leader();
$id = (int)($_GET['id'] ?? 0);

// PB-08: only the club leader who owns the hosting club can open this page.
$q = $pdo->prepare('SELECT e.*, c.name AS club_name, c.id AS club_id
    FROM events e JOIN clubs c ON c.id = e.club_id
    WHERE e.id = ? AND c.leader_id = ?');
$q->execute([$id, current_user()['id']]);
$event = $q->fetch();
if (!$event) { http_response_code(403); exit('Access denied. Only the leader of the hosting club can view this attendee list.'); }

// PB-08: join registrations with users for this event.
$q = $pdo->prepare('SELECT r.id AS registration_id, r.registered_at, u.name, u.email
    FROM registrations r JOIN users u ON u.id = r.user_id
    WHERE r.event_id = ?
    ORDER BY r.registered_at ASC');
$q->execute([$id]);
$attendees = $q->fetchAll();

$total = count($attendees);
$capacity = (int)$event['capacity'];

$pageTitle = 'Attendees - ' . $event['title'];
require 'header.php';
?>
<section class="page-intro">
  <span class="eyebrow">EVENT ATTENDEES · <?=e($event['club_name'])?></span>
  <h1><?=e($event['title'])?></h1>
  <p>📍 <?=e($event['venue'])?> · <?=date('l, M j, Y g:i A', strtotime($event['event_date']))?></p>
  <div class="badge-row">
    <span class="capacity-badge <?= $total >= $capacity ? 'full' : '' ?>"><?=$total?> / <?=$capacity?> Registered</span>
  </div>
  <a class="text-link" href="manage-club.php?id=<?=$event['club_id']?>">← Back to manage club</a>
</section>

<?php if (!$attendees): ?>
  <div class="empty">No students registered for this event yet.</div>
<?php else: ?>
<div class="table-card">
  <div class="table-scroll">
  <table class="attendee-table">
    <thead><tr><th>#</th><th>Student name</th><th>University email</th><th>Registered at</th></tr></thead>
    <tbody>
    <?php foreach ($attendees as $i => $a): ?>
      <tr>
        <td><?=$i + 1?></td>
        <td><strong><?=e($a['name'])?></strong></td>
        <td><?=e($a['email'])?></td>
        <td><?=date('M j, Y g:i A', strtotime($a['registered_at']))?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
