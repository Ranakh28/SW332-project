<?php
// Sprint 2 - PB-10: a student hides ("Mark as read") one of her event reminders.
require 'helpers.php';          // helpers.php runs verify_csrf() on every POST request
require_student();               // logged in + student role

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();                   // explicit CSRF check for this action

$registrationId = (int)($_POST['registration_id'] ?? 0);
$return = safe_return_url($_POST['return'] ?? '');

// The registration must belong to the current student.
$q = $pdo->prepare('SELECT id FROM registrations WHERE id = ? AND user_id = ?');
$q->execute([$registrationId, current_user()['id']]);
if (!$q->fetch()) {
    flash('This reminder could not be found for your account.', 'error');
    redirect($return);
}

$q = $pdo->prepare('UPDATE registrations SET reminder_dismissed = 1 WHERE id = ? AND user_id = ?');
$q->execute([$registrationId, current_user()['id']]);
flash('Reminder marked as read.');
redirect($return);
