<?php
// Update these values to match your XAMPP MySQL setup.
$host = 'localhost';
$db   = 'campusconnect';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
try { $pdo = new PDO($dsn, $user, $pass, $options); }
catch (PDOException $e) { die('Database connection failed. Import database.sql in phpMyAdmin and check config.php.'); }
if (session_status() === PHP_SESSION_NONE) session_start();
const UNIVERSITY_EMAIL_DOMAIN = 'pnu.edu.sa'; // Change if your university uses another domain.
?>
