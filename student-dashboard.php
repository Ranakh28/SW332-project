<?php
// Sprint 2 - PB-11: personal dashboard of joined clubs and registered events.
require 'helpers.php';
require_student(); // only authenticated student accounts can open this page
$userId = current_user()['id'];

$pageTitle = 'My Dashboard';
require 'header.php';
?>
<section class="page-intro">
  <span class="eyebrow">STUDENT SPACE</span>
  <h1>My Dashboard</h1>
  <p>Hi <?=e(current_user()['name'])?>, here are your clubs and your event schedule.</p>
</section>

<div class="dashboard-grid">
  <section><h2>My Joined Clubs</h2></section>
  <section><h2>My Registered Events</h2></section>
</div>
<?php require 'footer.php'; ?>
