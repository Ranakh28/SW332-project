# CampusConnect

CampusConnect is a web application for university student clubs. Students discover clubs, join them and register for events. Club leaders create clubs and events, view who registered and record attendance.

**SW332 – Group G2, Section O52** · Princess Nourah bint Abdulrahman University
Product Owner: Shahad Alaqeel · Scrum Master: Fatimah Albasha · Development Team: Rana Khalid Alharbi, Maha Alfifi, Aljawharah Saleh Almutlaq

Built with PHP 8, MySQL/MariaDB (XAMPP), HTML and CSS.

## Features

| PBI | Feature | Sprint |
|---|---|---|
| PB-01 | Student account registration with university e-mail (@pnu.edu.sa) and hashed password | 1 |
| PB-02 | Log in / log out with PHP sessions | 1 |
| PB-03 | Club leader creates a club profile | 1 |
| PB-04 | Students browse clubs and join a club | 1 |
| PB-05 | Club leader creates events (date, venue, capacity) | 1 |
| PB-06 | Students view upcoming events sorted by date | 1 |
| PB-07 | One-click event registration with duplicate prevention and capacity check | 1 |
| PB-08 | Club leader views registered students of an event (name, e-mail, time, registered / capacity) – owner only | 2 |
| PB-09 | Digital attendance: Pending / Present / Absent per student, one Save, live counters | 2 |
| PB-10 | 24-hour event reminders for registered students, with "Mark as read" | 2 |
| PB-11 | Student dashboard: My Joined Clubs and My Registered Events with Upcoming/Past and attendance badges | 2 |

Security: PDO prepared statements, CSRF tokens on every form, output escaping, role checks (student / club leader) and ownership checks on club pages.

## Installation (XAMPP)

1. Copy the `CampusConnect` folder to `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` → **Import** → `database.sql` → **Go**.
   *Existing Sprint 1 database?* Select `campusconnect` → **Import** → `sprint2_upgrade.sql` instead (keeps all data).
4. Select `campusconnect` → **Import** → `demo_data.sql` → **Go** (optional demo data).
5. Open `http://localhost/CampusConnect/`.

Database settings are in `config.php` (default XAMPP: `localhost`, `root`, empty password, database `campusconnect`).

## Demo accounts (`demo_data.sql`)

Password for every account: **Campus@2026**

| Role | E-mail | Notes |
|---|---|---|
| Club leader | noura.alshehri@pnu.edu.sa | Software Engineering Club, Volunteering Club |
| Club leader | haya.alotaibi@pnu.edu.sa | Arabic Literature Club |
| Club leader | dana.alsubaie@pnu.edu.sa | Photography Club |
| Club leader | rahaf.alenazi@pnu.edu.sa | Health & Wellness Club |
| Student | sara.alqahtani@pnu.edu.sa | Joined 4 clubs, has 2 reminders in the next 24 hours |
| Students | lama.aldosari, reem.alzahrani, joud.alharthi, ghada.almutairi, nouf.alshammari, shatha.alanazi, wejdan.albaqami, amal.alghamdi, razan.alrashidi (@pnu.edu.sa) | |

Event dates are relative to the import time. Re-import `demo_data.sql` on the demo day so the 24-hour reminders appear.

## Project structure

```
CampusConnect/
├── index.php, clubs.php, club.php, events.php, event.php      Public pages
├── register.php, login.php, logout.php                        Accounts (PB-01, PB-02)
├── dashboard.php, manage-club.php                             Club leader pages (PB-03, PB-05)
├── event-attendees.php                                        Attendees + attendance (PB-08, PB-09)
├── student-dashboard.php, dismiss-reminder.php                Student dashboard + reminders (PB-10, PB-11)
├── header.php, footer.php, helpers.php, config.php            Layout, helpers, DB connection
├── assets/style.css
├── database.sql            Full schema (new installation)
├── sprint2_upgrade.sql     Upgrade a Sprint 1 database to Sprint 2
├── demo_data.sql           Realistic demo data
```

## Time zone

The application uses `Asia/Riyadh`. `config.php` sets PHP and the MySQL session to the same time zone so reminders and Upcoming/Past labels are correct.
