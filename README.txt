CAMPUSCONNECT - PHP + MySQL (XAMPP)

FEATURES INCLUDED (matching Sprint Backlog PB-01 to PB-07)
PB-01 Student account registration with university email validation and hashed password.
PB-02 Login/logout with PHP sessions.
PB-03 Club leader creates a club profile.
PB-04 Students browse clubs and join a club.
PB-05 Club leaders create events (date, venue, capacity).
PB-06 Students view upcoming events sorted by date.
PB-07 One-click event registration, duplicate prevention, and capacity checking.

REQUIREMENTS
- XAMPP with Apache and MySQL
- PHP 8.0+ recommended

SETUP
1. Copy the CampusConnect folder into: C:\xampp\htdocs\
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open http://localhost/phpmyadmin
4. Choose Import and import database.sql from this folder.
5. Check config.php. Default XAMPP settings are host=localhost, database=campusconnect, user=root, empty password.
6. Open http://localhost/CampusConnect/

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
- QR attendance, reminders, analytics, and Arabic/English toggle are listed in the wider product vision but are not part of the supplied PB-01 to PB-07 sprint backlog, so they are not included in this sprint build.
