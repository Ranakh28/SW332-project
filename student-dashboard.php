<?php
// Sprint 2 - PB-11: personal dashboard of joined clubs and registered events.
require 'helpers.php';
require_student(); // only authenticated student accounts can open this page
$userId = current_user()['id'];

// PB-11: clubs the student joined (memberships + clubs).
$q = $pdo->prepare('SELECT c.id, c.name, c.category, c.description, m.joined_at,
        (SELECT COUNT(*) FROM memberships m2 WHERE m2.club_id = c.id) AS members
    FROM memberships m JOIN clubs c ON c.id = m.club_id
    WHERE m.user_id = ?
    ORDER BY m.joined_at DESC');
$q->execute([$userId]);
$clubs = $q->fetchAll();

// PB-11: events the student registered for (registrations + events + clubs), ordered by date.
// is_upcoming is decided by MySQL NOW() (Asia/Riyadh session time zone, see config.php).
$q = $pdo->prepare('SELECT r.id AS registration_id, r.registered_at, r.attendance_status,
        e.id AS event_id, e.title, e.venue, e.event_date,
        c.id AS club_id, c.name AS club_name,
        (e.event_date >= NOW()) AS is_upcoming
    FROM registrations r
    JOIN events e ON e.id = r.event_id
    JOIN clubs c ON c.id = e.club_id
    WHERE r.user_id = ?
    ORDER BY e.event_date ASC');
$q->execute([$userId]);
$events = $q->fetchAll();

// Upcoming: soonest first. Past: most recent first. Both lists stay ordered by event date.
$upcoming = array_values(array_filter($events, fn($ev) => (int)$ev['is_upcoming'] === 1));
$past = array_reverse(array_values(array_filter($events, fn($ev) => (int)$ev['is_upcoming'] !== 1)));
$attended = count(array_filter($events, fn($ev) => $ev['attendance_status'] === 'Present'));

$pageTitle = 'My Dashboard';
$remindersInPage = true; // PB-10: this page prints reminders itself, the header banner is skipped
require 'header.php';     // also loads $headerReminders via get_upcoming_reminders()

function render_student_event($ev) {
    $isUpcoming = (int)$ev['is_upcoming'] === 1;
    $status = in_array($ev['attendance_status'], ATTENDANCE_STATUSES, true) ? $ev['attendance_status'] : 'Pending';
    $ts = strtotime($ev['event_date']); ?>
    <article class="event-card dash-event <?= $isUpcoming ? '' : 'is-past' ?>">
      <div class="event-date"><b><?=date('d', $ts)?></b><span><?=date('M', $ts)?></span></div>
      <div class="event-content">
        <div class="badge-row tight">
          <span class="status-badge <?= $isUpcoming ? 'upcoming' : 'past' ?>"><?= $isUpcoming ? 'Upcoming' : 'Past' ?></span>
          <span class="status-badge <?=strtolower($status)?>"><?=e($status)?></span>
        </div>
        <h3><a href="event.php?id=<?=(int)$ev['event_id']?>"><?=e($ev['title'])?></a></h3>
        <p><a class="text-link" href="club.php?id=<?=(int)$ev['club_id']?>"><?=e($ev['club_name'])?></a> · 📍 <?=e($ev['venue'])?></p>
        <p><?=date('D, M j, Y g:i A', $ts)?></p>
      </div>
    </article>
<?php }
?>
<section class="page-intro">
  <span class="eyebrow">STUDENT SPACE</span>
  <h1>My Dashboard</h1>
  <p>Hi <?=e(current_user()['name'])?>, here are your clubs and your event schedule.</p>
</section>

<section class="stats dash-stats">
  <div><strong><?=count($clubs)?></strong><span>Joined clubs</span></div>
  <div><strong><?=count($upcoming)?></strong><span>Upcoming events</span></div>
  <div><strong><?=$attended?></strong><span>Events attended</span></div>
</section>

<?php render_reminders($headerReminders, 'dashboard'); ?>

<div class="dashboard-grid student-grid">
  <section>
    <div class="section-head compact"><div><span class="eyebrow">YOUR COMMUNITIES</span><h2>My Joined Clubs</h2></div><a class="text-link" href="clubs.php">Browse clubs →</a></div>
    <?php if (!$clubs): ?>
      <div class="empty">You have not joined any clubs yet. <a class="text-link" href="clubs.php">Explore student clubs →</a></div>
    <?php endif; ?>
    <?php foreach ($clubs as $c): ?>
      <article class="list-card">
        <div>
          <span class="tag"><?=e($c['category'] ?: 'Student community')?></span>
          <h3><?=e($c['name'])?></h3>
          <p><?=(int)$c['members']?> member<?=(int)$c['members'] === 1 ? '' : 's'?> · Joined <?=date('M j, Y', strtotime($c['joined_at']))?></p>
        </div>
        <a class="text-link" href="club.php?id=<?=(int)$c['id']?>">View club →</a>
      </article>
    <?php endforeach; ?>
  </section>

  <section>
    <div class="section-head compact"><div><span class="eyebrow">YOUR SCHEDULE</span><h2>My Registered Events</h2></div><a class="text-link" href="events.php">Find events →</a></div>
    <?php if (!$events): ?>
      <div class="empty">You have not registered for any events yet. <a class="text-link" href="events.php">See upcoming events →</a></div>
    <?php else: ?>
      <h3 class="list-label">Upcoming</h3>
      <?php if (!$upcoming): ?><div class="empty">No upcoming events. <a class="text-link" href="events.php">Find your next event →</a></div><?php endif; ?>
      <div class="event-list compact"><?php foreach ($upcoming as $ev) render_student_event($ev); ?></div>
      <?php if ($past): ?>
        <h3 class="list-label">Past</h3>
        <div class="event-list compact"><?php foreach ($past as $ev) render_student_event($ev); ?></div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
<?php require 'footer.php'; ?>
