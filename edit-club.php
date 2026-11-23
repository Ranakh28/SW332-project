<?php
require 'helpers.php';
require_leader();
$id = (int)($_GET['id'] ?? 0);

$q = $pdo->prepare('SELECT * FROM clubs WHERE id = ? AND leader_id = ?');
$q->execute([$id, current_user()['id']]);
$club = $q->fetch();
if (!$club) { http_response_code(404); exit('Club not found or access denied.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {   
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name === '') {
        flash('Club name is required.', 'error');
        redirect('edit-club.php?id=' . $id);
    }
    $q = $pdo->prepare('UPDATE clubs SET name = ?, category = ?, description = ? WHERE id = ? AND leader_id = ?');
    $q->execute([$name, $category, $description, $id, current_user()['id']]);
    flash('Your club profile has been updated.');
    redirect('manage-club.php?id=' . $id);
}

$pageTitle = 'Edit club';
require 'header.php';
?>
<section class="page-intro">
  <span class="eyebrow">CLUB PROFILE</span>
  <h1>Edit <?=e($club['name'])?></h1>
  <a class="text-link" href="manage-club.php?id=<?=$id?>">← Back to manage club</a>
</section>
<form class="form-card" method="post" style="margin-bottom:60px">
  <?php csrf_field(); ?>
  <label>Club name<input name="name" value="<?=e($club['name'])?>" required></label>
  <label>Category<input name="category" value="<?=e($club['category'])?>" placeholder="Technology, Art, Sports..."></label>
  <label>Description<textarea name="description" rows="5"><?=e($club['description'])?></textarea></label>
  <button class="btn primary full">Save changes</button>
</form>
<?php require 'footer.php'; ?>
