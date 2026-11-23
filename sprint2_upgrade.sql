-- =====================================================================
-- CampusConnect - Sprint 2 database upgrade (PB-09 attendance, PB-10 reminders)
-- Upgrades an EXISTING Sprint 1 database. It does NOT drop or delete any data.
-- Safe to run more than once: each column is added only if it is missing.
-- Works on MariaDB (XAMPP) and MySQL 5.7/8.x (no DELIMITER or procedures needed).
--
-- How to run (XAMPP):
--   1. Back up first: phpMyAdmin > campusconnect > Export > Go.
--   2. phpMyAdmin > campusconnect > Import > choose sprint2_upgrade.sql > Go.
--   3. Check the last result: both columns must be listed.
-- New installation? Import database.sql instead (it already contains these columns).
-- =====================================================================
USE campusconnect;

-- Task 9.1: registrations.attendance_status
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registrations' AND COLUMN_NAME = 'attendance_status');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE registrations ADD COLUMN attendance_status ENUM(''Pending'',''Present'',''Absent'') NOT NULL DEFAULT ''Pending'' AFTER registered_at',
  'SELECT ''attendance_status already exists - skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Task 9.1 / PB-10: registrations.reminder_dismissed
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registrations' AND COLUMN_NAME = 'reminder_dismissed');
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE registrations ADD COLUMN reminder_dismissed TINYINT(1) NOT NULL DEFAULT 0 AFTER attendance_status',
  'SELECT ''reminder_dismissed already exists - skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Task 9.3: one registration (and therefore one attendance record) per student per event.
-- Sprint 1 already created this key; it is only added here if it is missing.
SET @idx_exists := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registrations' AND INDEX_NAME = 'unique_registration');
SET @sql := IF(@idx_exists = 0,
  'ALTER TABLE registrations ADD UNIQUE KEY unique_registration (user_id, event_id)',
  'SELECT ''unique_registration already exists - skipped'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Verification: expect attendance_status and reminder_dismissed in the result.
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'registrations'
  AND COLUMN_NAME IN ('attendance_status', 'reminder_dismissed');

-- Existing registrations keep their data and start as attendance_status = 'Pending', reminder_dismissed = 0.
SELECT COUNT(*) AS registrations_kept FROM campusconnect.registrations;
