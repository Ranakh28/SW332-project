<?php require_once __DIR__.'/helpers.php'; $pageTitle = $pageTitle ?? 'CampusConnect';
// Sprint 2 - PB-10: load reminders for logged-in students only.
$headerReminders = [];
if (is_logged_in() && current_user()['role'] === 'student') {
    try { $headerReminders = get_upcoming_reminders(current_user()['id']); }
    catch (PDOException $ex) { error_log('Reminders unavailable (run sprint2_upgrade.sql): ' . $ex->getMessage()); }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($pageTitle)?> | CampusConnect</title><link rel="stylesheet" href="assets/style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">C</span> CampusConnect</a><nav>
<a href="clubs.php">Clubs</a><a href="events.php">Events</a>
<?php if (is_logged_in()): ?><?php if(current_user()['role']==='club_leader'): ?><a href="dashboard.php">Dashboard</a><?php else: ?><a href="student-dashboard.php">My Dashboard<?php if($headerReminders): ?> <span class="nav-count" title="Event reminders"><?=count($headerReminders)?></span><?php endif; ?></a><?php endif; ?><span class="welcome">Hi, <?=e(current_user()['name'])?></span><a class="nav-button" href="logout.php">Log out</a><?php else: ?><a href="login.php">Log in</a><a class="nav-button" href="register.php">Join us</a><?php endif; ?>
</nav></header><main class="container"><?php show_flash(); ?>
<?php // PB-10: the student dashboard shows reminders in its own section, so the header banner is skipped there.
if (empty($remindersInPage)) render_reminders($headerReminders, 'banner'); ?>
