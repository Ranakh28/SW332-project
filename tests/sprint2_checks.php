<?php
/**
 * CampusConnect - Sprint 2 automatic checks (Task 10.3 + Task 9.1). READ-ONLY: it changes nothing.
 * Run from the command line only, after importing sprint2_test_data.sql:
 *   Windows/XAMPP:  C:\xampp\php\php.exe C:\xampp\htdocs\CampusConnect\tests\sprint2_checks.php
 *   macOS/Linux:    php tests/sprint2_checks.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // never runs through the browser

require __DIR__ . '/../helpers.php';

$pass = 0; $fail = 0;
function check($label, $ok, $detail = '') {
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . ($detail !== '' ? "  ($detail)" : '') . PHP_EOL;
}

echo "== Time zone (PHP vs MySQL) ==" . PHP_EOL;
$phpNow = date('Y-m-d H:i:s');
$row = $pdo->query("SELECT NOW() AS db_now, @@session.time_zone AS tz")->fetch();
$diff = abs(strtotime($row['db_now']) - strtotime($phpNow));
check('PHP time zone is Asia/Riyadh', date_default_timezone_get() === 'Asia/Riyadh', date_default_timezone_get());
check('MySQL session time zone is +03:00', $row['tz'] === '+03:00', $row['tz']);
check('PHP and MySQL NOW() match (<= 5 s)', $diff <= 5, "PHP $phpNow / MySQL {$row['db_now']}");

echo PHP_EOL . "== Database columns (Task 9.1) ==" . PHP_EOL;
$cols = $pdo->query("SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registrations'")->fetchAll(PDO::FETCH_UNIQUE);
check('attendance_status exists', isset($cols['attendance_status']));
check("attendance_status is ENUM('Pending','Present','Absent') NOT NULL DEFAULT 'Pending'",
    isset($cols['attendance_status']) && $cols['attendance_status']['COLUMN_TYPE'] === "enum('Pending','Present','Absent')"
    && $cols['attendance_status']['IS_NULLABLE'] === 'NO' && trim((string)$cols['attendance_status']['COLUMN_DEFAULT'], "'") === 'Pending');
check('reminder_dismissed exists (TINYINT(1) NOT NULL DEFAULT 0)',
    isset($cols['reminder_dismissed']) && stripos($cols['reminder_dismissed']['COLUMN_TYPE'], 'tinyint') === 0
    && $cols['reminder_dismissed']['IS_NULLABLE'] === 'NO' && trim((string)$cols['reminder_dismissed']['COLUMN_DEFAULT'], "'") === '0');
$dupes = (int)$pdo->query('SELECT COUNT(*) FROM (SELECT user_id, event_id FROM registrations GROUP BY user_id, event_id HAVING COUNT(*) > 1) d')->fetchColumn();
check('No duplicate registration/attendance rows', $dupes === 0, "$dupes duplicates");

echo PHP_EOL . "== Reminders for test.student1 (Task 10.3) ==" . PHP_EOL;
$q = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$q->execute(['test.student1@pnu.edu.sa']);
$studentId = $q->fetchColumn();
if (!$studentId) {
    echo "test.student1@pnu.edu.sa not found. Import sprint2_test_data.sql first." . PHP_EOL;
} else {
    $titles = array_column(get_upcoming_reminders($studentId), 'title');
    echo 'Returned: ' . ($titles ? implode(' | ', $titles) : '(none)') . PHP_EOL;
    check('Event in 12 hours IS shown',       in_array('[TEST] Starts in 12 hours', $titles, true));
    check('Event in 48 hours is NOT shown',   !in_array('[TEST] Starts in 48 hours', $titles, true));
    check('Ended event is NOT shown',         !in_array('[TEST] Ended 3 hours ago', $titles, true));
    check('Dismissed reminder is NOT shown',  !in_array('[TEST] Dismissed in 6 hours', $titles, true));
    check('Other students\' events are NOT shown', !in_array('[TEST] Club B private list', $titles, true)
        && !in_array('[TEST] Full event (capacity 1)', $titles, true));
}

echo PHP_EOL . "Result: $pass passed, $fail failed" . PHP_EOL;
exit($fail ? 1 : 0);
