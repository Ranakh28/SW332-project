<?php
require_once __DIR__ . '/config.php';
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function is_logged_in() { return !empty($_SESSION['user']); }
function current_user() { return $_SESSION['user'] ?? null; }
function require_login() { if (!is_logged_in()) redirect('login.php'); }
function require_leader() { require_login(); if (current_user()['role'] !== 'club_leader') { http_response_code(403); exit('Access denied. Club leaders only.'); } }
function flash($message, $type='success') { $_SESSION['flash'] = ['message'=>$message, 'type'=>$type]; }
function show_flash() { if (!empty($_SESSION['flash'])) { $f=$_SESSION['flash']; echo '<div class="alert '.e($f['type']).'">'.e($f['message']).'</div>'; unset($_SESSION['flash']); } }
function csrf_field() { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); echo '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verify_csrf() { if ($_SERVER['REQUEST_METHOD']==='POST' && (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']))) { http_response_code(419); exit('Invalid request. Refresh the page and try again.'); } }
verify_csrf();
?>
