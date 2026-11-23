<?php
require_once __DIR__ . '/config.php';
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function is_logged_in() { return !empty($_SESSION['user']); }
function current_user() { return $_SESSION['user'] ?? null; }
function require_login() { if (!is_logged_in()) redirect('login.php'); }
function require_leader() { require_login(); if (current_user()['role'] !== 'club_leader') { http_response_code(403); exit('Access denied. Club leaders only.'); } }
function require_student() { require_login(); if (current_user()['role'] !== 'student') { http_response_code(403); exit('Access denied. Student accounts only.'); } }
function flash($message, $type='success') { $_SESSION['flash'] = ['message'=>$message, 'type'=>$type]; }
function show_flash() { if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; echo '<div class="alert '.e($f['type']).'">'.e($f['message']).'</div>'; unset($_SESSION['flash']); } }
function csrf_field() { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); echo '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verify_csrf() { if (($_SERVER['REQUEST_METHOD'] ?? 'GET')==='POST' && (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf']))) { http_response_code(419); exit('Invalid request. Refresh the page and try again.'); } }
verify_csrf();


const ATTENDANCE_STATUSES = ['Pending', 'Present', 'Absent'];


function get_upcoming_reminders($user_id) {
    global $pdo;
    $q = $pdo->prepare('SELECT r.id AS registration_id, e.id AS event_id, e.title, e.venue, e.event_date,
            c.id AS club_id, c.name AS club_name
        FROM registrations r
        JOIN events e ON e.id = r.event_id
        JOIN clubs c ON c.id = e.club_id
        WHERE r.user_id = ?
          AND r.reminder_dismissed = 0
          AND e.event_date > NOW()
          AND e.event_date <= DATE_ADD(NOW(), INTERVAL 24 HOUR)
        ORDER BY e.event_date ASC');
    $q->execute([(int)$user_id]);
    return $q->fetchAll();
}

function time_remaining_label($eventDate) {
    $seconds = strtotime($eventDate) - time();
    if ($seconds <= 0) return 'Started';
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    if ($hours === 0 && $minutes === 0) return 'Starts in less than a minute';
    if ($hours === 0) return 'Starts in ' . $minutes . ' min';
    return 'Starts in ' . $hours . ' h ' . $minutes . ' min';
}

function current_page_url() {
    $page = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $query = $_SERVER['QUERY_STRING'] ?? '';
    return $query === '' ? $page : $page . '?' . $query;
}

function safe_return_url($url, $fallback = 'student-dashboard.php') {
    $url = (string)$url;
    return preg_match('/^[a-z0-9\-]+\.php(\?[A-Za-z0-9=&_\-]*)?(#[a-z0-9\-]+)?$/', $url) ? $url : $fallback;
}


function render_reminders(array $reminders, $variant = 'banner') {
    static $rendered = false;
    if ($rendered || !$reminders) return;
    $rendered = true;
    $return = $variant === 'dashboard' ? 'student-dashboard.php' : current_page_url();
    echo '<section class="reminders reminders-' . e($variant) . '" id="reminders" aria-label="Event reminders">';
    echo '<div class="reminders-head"><span class="eyebrow">🔔 EVENT REMINDERS</span><span class="muted">'
        . count($reminders) . ' event' . (count($reminders) === 1 ? '' : 's') . ' in the next 24 hours</span></div>';
    foreach ($reminders as $r) {
        echo '<article class="reminder-card">'
            . '<div class="reminder-body">'
            . '<span class="reminder-time">' . e(time_remaining_label($r['event_date'])) . '</span>'
            . '<h3><a href="event.php?id=' . (int)$r['event_id'] . '">' . e($r['title']) . '</a></h3>'
            . '<p><a class="text-link" href="club.php?id=' . (int)$r['club_id'] . '">' . e($r['club_name']) . '</a>'
            . ' · 📍 ' . e($r['venue'])
            . ' · ' . e(date('D, M j, Y g:i A', strtotime($r['event_date']))) . '</p>'
            . '</div>'
            . '<form method="post" action="dismiss-reminder.php">';
        csrf_field();
        echo '<input type="hidden" name="registration_id" value="' . (int)$r['registration_id'] . '">'
            . '<input type="hidden" name="return" value="' . e($return) . '">'
            . '<button class="btn ghost small">Mark as read</button>'
            . '</form></article>';
    }
    echo '</section>';
}
?>
