<?php
// Update these values to match your XAMPP MySQL setup.
$host = 'localhost';
$db   = 'campusconnect';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Sprint 2 - PB-10: one time zone for PHP and MySQL so reminders and Upcoming/Past are correct.
const APP_TIMEZONE = 'Asia/Riyadh';
date_default_timezone_set(APP_TIMEZONE);

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Use a numeric offset (e.g. +03:00) because XAMPP's MariaDB usually has no named time zone tables.
    // NOW() then matches PHP time, and TIMESTAMP columns (registered_at) are shown in Riyadh time.
    // DATETIME columns (event_date) are stored exactly as the leader typed them (Riyadh local time).
    $pdo->exec("SET time_zone = '" . (new DateTime('now', new DateTimeZone(APP_TIMEZONE)))->format('P') . "'");
}
catch (PDOException $e) { die('Database connection failed. Import database.sql in phpMyAdmin and check config.php.'); }
if (session_status() === PHP_SESSION_NONE) session_start();
const UNIVERSITY_EMAIL_DOMAIN = 'pnu.edu.sa'; // Change if your university uses another domain.
?>
