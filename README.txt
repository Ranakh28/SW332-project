CAMPUSCONNECT - PHP + MySQL (XAMPP) - SPRINT 2

FEATURES INCLUDED (matching Sprint Backlog PB-01 to PB-07)
PB-01 Student account registration with university email validation and hashed password.
PB-02 Login/logout with PHP sessions.
PB-03 Club leader creates a club profile.
PB-04 Students browse clubs and join a club.
PB-05 Club leaders create events (date, venue, capacity).
PB-06 Students view upcoming events sorted by date.
PB-07 One-click event registration, duplicate prevention, and capacity checking.

SPRINT 2 FEATURES (PB-08 to PB-11)
PB-08 Club leader views the registered students of his/her own club's event (name, university email,
      registration time, registered / capacity). Opened from Manage club > "View Attendees".
PB-09 Digital attendance on the same page: Pending / Present / Absent per student, one "Save Attendance"
      button (single transaction), live Present / Absent / Pending counters.
PB-10 Event reminders: students see events they registered for that start within the next 24 hours
      (header banner on every page + section on My Dashboard) and can "Mark as read".
PB-11 Student dashboard (My Dashboard): My Joined Clubs + My Registered Events with Upcoming/Past and
      attendance badges.

REQUIREMENTS
- XAMPP with Apache and MySQL
- PHP 8.0+ recommended

SETUP - NEW INSTALLATION (no CampusConnect database yet)
1. Copy the CampusConnect folder into: C:\xampp\htdocs\
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open http://localhost/phpmyadmin
4. Choose Import and import database.sql from this folder (it already includes the Sprint 2 columns).
5. Check config.php. Default XAMPP settings are host=localhost, database=campusconnect, user=root, empty password.
6. Open http://localhost/CampusConnect/

SETUP - UPGRADE FROM SPRINT 1 (keep all existing users, clubs, events and registrations)
1. Replace the old project files in C:\xampp\htdocs\CampusConnect\ with this version
   (keep your own config.php values for host/user/password if you changed them).
2. phpMyAdmin > campusconnect > Export > Go   (backup first).
3. phpMyAdmin > campusconnect > Import > sprint2_upgrade.sql > Go.
   It adds registrations.attendance_status and registrations.reminder_dismissed only if they are missing,
   so running it twice is safe. Nothing is deleted. Old registrations become 'Pending' / not dismissed.
4. The last result of the import must list attendance_status and reminder_dismissed.
   Do NOT re-import database.sql on an existing database: CREATE TABLE IF NOT EXISTS will not add new columns.

TIME ZONE (PB-10)
config.php sets PHP to Asia/Riyadh and runs SET time_zone = '+03:00' on every MySQL connection, so NOW() in
MySQL and time() in PHP match. Event dates (DATETIME) are stored as the Riyadh local time the leader typed.

DEMO DATA (recommended before a demo)
phpMyAdmin > campusconnect > Import > demo_data.sql
Adds 5 clubs, 13 events, 4 club leaders and 10 students with registrations and attendance.
Password for every demo account: Campus@2026 (e.g. noura.alshehri@pnu.edu.sa, sara.alqahtani@pnu.edu.sa).
Event times are relative to the import time, so re-import it on the demo day to refresh the 24-hour reminders.

SPRINT 2 TEST DATA AND CHECKS (optional, local only)
1. phpMyAdmin > campusconnect > Import > tests/sprint2_test_data.sql
   Creates test.leader1 / test.leader2 / test.student1 / test.student2 @pnu.edu.sa (password: Test@1234),
   two TEST clubs and "[TEST]" events at 12 h, 48 h, -3 h (ended), 6 h (dismissed) and a full event.
   Re-import it before a demo: the times are relative to the moment of import.
2. Command line (read-only checks of the time zone, the new columns and the 4 reminder cases):
   C:\xampp\php\php.exe C:\xampp\htdocs\CampusConnect\tests\sprint2_checks.php
   Every line must say [PASS]. The script refuses to run from the browser.
3. Full manual test steps: docs/SPRINT2_DELIVERY.md, section 5.

UNIVERSITY EMAIL
Registration currently accepts addresses ending in @pnu.edu.sa. If your university uses another domain, change UNIVERSITY_EMAIL_DOMAIN in config.php.

ACCOUNT TYPES
1. On the registration page, choose Student or Club Leader.
2. Student accounts can browse/join clubs and register for events.
3. Club Leader accounts are sent to the dashboard after login and can create/manage their own clubs and events.
4. Club leaders cannot join clubs or register for events as students.

EXISTING ACCOUNT / PROMOTE AN ACCOUNT
Accounts created before this update remain Students. To change an existing account to Club Leader, open phpMyAdmin > campusconnect > SQL and run (replace the email):
   UPDATE users SET role='club_leader' WHERE email='leader@pnu.edu.sa';
Then log out and log in again so the new role is loaded into the session.

NOTE: The role selector is suitable for this class-project demonstration. A real campus deployment should have an administrator approve Club Leader accounts instead of allowing unrestricted self-selection.

NOTES
- This is a student project starter, not a production deployment.
- Sprint 2 adds attendance (PB-09) and 24-hour reminders (PB-10). Reminders are shown inside the website when
  the student opens it; no e-mail/SMS/push notifications are sent.
- Category filter, QR check-in, attendance reports and the Arabic/English toggle (PB-12 to PB-15) are Sprint 3
  items and are not included in this build.
