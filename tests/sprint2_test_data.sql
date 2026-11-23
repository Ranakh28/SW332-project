-- =====================================================================
-- CampusConnect - Sprint 2 TEST DATA (local XAMPP testing only, optional)
-- Run AFTER database.sql (new install) or sprint2_upgrade.sql (existing DB).
-- It only ADDS test rows. It never touches your real Sprint 1 users, clubs or events.
-- Re-running it refreshes only the "[TEST]" events inside the two TEST clubs below,
-- so the reminder times are always relative to the moment you import it.
--
-- All test accounts use the password:  Test@1234
--   test.leader1@pnu.edu.sa   Club Leader (owns "TEST Club A")
--   test.leader2@pnu.edu.sa   Club Leader (owns "TEST Club B") - used for access-denied tests
--   test.student1@pnu.edu.sa  Student (main reminder / dashboard tester)
--   test.student2@pnu.edu.sa  Student
-- =====================================================================
USE campusconnect;
SET time_zone = '+03:00';  -- same as config.php (Asia/Riyadh), so NOW() below is Riyadh time

-- Users (skipped if the email already exists)
INSERT IGNORE INTO users(name, email, password, role) VALUES
 ('Test Leader One',  'test.leader1@pnu.edu.sa',  '$2y$10$ZFhB6UTt2DygeNE0bGPljOYh8PiiWGiOCSducpvgou4cNLn1rDsMa', 'club_leader'),
 ('Test Leader Two',  'test.leader2@pnu.edu.sa',  '$2y$10$ZFhB6UTt2DygeNE0bGPljOYh8PiiWGiOCSducpvgou4cNLn1rDsMa', 'club_leader'),
 ('Test Student One', 'test.student1@pnu.edu.sa', '$2y$10$ZFhB6UTt2DygeNE0bGPljOYh8PiiWGiOCSducpvgou4cNLn1rDsMa', 'student'),
 ('Test Student Two', 'test.student2@pnu.edu.sa', '$2y$10$ZFhB6UTt2DygeNE0bGPljOYh8PiiWGiOCSducpvgou4cNLn1rDsMa', 'student');

SET @leader1  := (SELECT id FROM users WHERE email = 'test.leader1@pnu.edu.sa');
SET @leader2  := (SELECT id FROM users WHERE email = 'test.leader2@pnu.edu.sa');
SET @student1 := (SELECT id FROM users WHERE email = 'test.student1@pnu.edu.sa');
SET @student2 := (SELECT id FROM users WHERE email = 'test.student2@pnu.edu.sa');

-- Clubs (created once)
INSERT INTO clubs(leader_id, name, category, description)
SELECT @leader1, 'TEST Club A', 'Technology', 'Sprint 2 test club owned by Test Leader One.'
WHERE NOT EXISTS (SELECT 1 FROM clubs WHERE name = 'TEST Club A' AND leader_id = @leader1);
INSERT INTO clubs(leader_id, name, category, description)
SELECT @leader2, 'TEST Club B', 'Culture', 'Sprint 2 test club owned by Test Leader Two.'
WHERE NOT EXISTS (SELECT 1 FROM clubs WHERE name = 'TEST Club B' AND leader_id = @leader2);
SET @clubA := (SELECT id FROM clubs WHERE name = 'TEST Club A' AND leader_id = @leader1 LIMIT 1);
SET @clubB := (SELECT id FROM clubs WHERE name = 'TEST Club B' AND leader_id = @leader2 LIMIT 1);

-- Refresh only the [TEST] events of the two TEST clubs (their test registrations are removed by ON DELETE CASCADE)
DELETE FROM events WHERE club_id IN (@clubA, @clubB) AND title LIKE '[TEST]%';

INSERT INTO events(club_id, title, description, venue, event_date, capacity) VALUES
 (@clubA, '[TEST] Starts in 12 hours',      'Reminder SHOULD appear.',              'Building A - Room 101', NOW() + INTERVAL 12 HOUR, 30),
 (@clubA, '[TEST] Starts in 48 hours',      'Reminder should NOT appear.',          'Building A - Room 102', NOW() + INTERVAL 48 HOUR, 30),
 (@clubA, '[TEST] Ended 3 hours ago',       'Past event - use it to mark attendance.', 'Main Hall',          NOW() - INTERVAL 3 HOUR,  30),
 (@clubA, '[TEST] Dismissed in 6 hours',    'Reminder already dismissed - should NOT appear.', 'Library',    NOW() + INTERVAL 6 HOUR,  30),
 (@clubA, '[TEST] Full event (capacity 1)', 'Capacity test - already full.',        'Lab 3',                 NOW() + INTERVAL 5 DAY,   1),
 (@clubB, '[TEST] Club B private list',     'Owned by Leader Two - Leader One must get Access denied.', 'Building B', NOW() + INTERVAL 2 DAY, 20);

SET @ev12     := (SELECT id FROM events WHERE club_id = @clubA AND title = '[TEST] Starts in 12 hours');
SET @ev48     := (SELECT id FROM events WHERE club_id = @clubA AND title = '[TEST] Starts in 48 hours');
SET @evPast   := (SELECT id FROM events WHERE club_id = @clubA AND title = '[TEST] Ended 3 hours ago');
SET @evDism   := (SELECT id FROM events WHERE club_id = @clubA AND title = '[TEST] Dismissed in 6 hours');
SET @evFull   := (SELECT id FROM events WHERE club_id = @clubA AND title = '[TEST] Full event (capacity 1)');
SET @evClubB  := (SELECT id FROM events WHERE club_id = @clubB AND title = '[TEST] Club B private list');

-- Registrations
INSERT IGNORE INTO registrations(user_id, event_id) VALUES
 (@student1, @ev12), (@student1, @ev48), (@student1, @evPast), (@student1, @evDism),
 (@student2, @ev12), (@student2, @evPast), (@student2, @evFull), (@student2, @evClubB);
UPDATE registrations SET reminder_dismissed = 1 WHERE user_id = @student1 AND event_id = @evDism;

-- Memberships (Student One joins Club A only; Student Two has none -> tests the empty state on her clubs list)
INSERT IGNORE INTO memberships(user_id, club_id) VALUES (@student1, @clubA);

-- Quick look: what get_upcoming_reminders() should return for Student One (expected: only "[TEST] Starts in 12 hours")
SELECT e.title, e.event_date, r.reminder_dismissed
FROM registrations r JOIN events e ON e.id = r.event_id
WHERE r.user_id = @student1 AND r.reminder_dismissed = 0
  AND e.event_date > NOW() AND e.event_date <= DATE_ADD(NOW(), INTERVAL 24 HOUR)
ORDER BY e.event_date;
