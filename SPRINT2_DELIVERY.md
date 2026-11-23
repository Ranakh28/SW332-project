# CampusConnect – Sprint 2 Delivery Report (PB-08 → PB-11)

Group G2 · SW332 · Prepared 20 Nov 2026 · Base: `SW332-project-main.zip` (latest GitHub code)

---

## 1. Starting point (what was found in the ZIP)

| Item | State found | Action taken |
|---|---|---|
| PB-01 → PB-07 (Sprint 1) | Complete | Kept. One bug fix in `event.php` (see §6). No file, table or function renamed. |
| PB-08 View registered students (Rana) | Mostly done: ownership check, name/email/registered time, `x / capacity` badge, empty state, link from `manage-club.php` | Kept Rana's code and structure; added the attendance column/form around it. |
| PB-09 Digital attendance | Not started (no columns, no form) | Implemented. |
| PB-10 Reminders | Not started (no `get_upcoming_reminders`, no `dismiss-reminder.php`, no time-zone setting) | Implemented. |
| PB-11 Student dashboard | Skeleton only (`require_student()` + two empty headings) | Implemented. |
| CSS | Sprint 2 classes for PB-08 already started | Appended new classes in the same palette (no existing rule changed). |

---

## 2. Files changed / created

| File | Status | What changed |
|---|---|---|
| `config.php` | Modified | `APP_TIMEZONE = 'Asia/Riyadh'`, `date_default_timezone_set()`, `SET time_zone = '+03:00'` on the PDO connection. DB credentials unchanged. |
| `helpers.php` | Modified | All Sprint 1 functions kept (names unchanged). `verify_csrf()` now tolerates CLI (no `REQUEST_METHOD`). Added `ATTENDANCE_STATUSES`, **`get_upcoming_reminders($user_id)`**, `time_remaining_label()`, `current_page_url()`, `safe_return_url()`, `render_reminders()`. |
| `database.sql` | Modified | `registrations` gets `attendance_status ENUM('Pending','Present','Absent') NOT NULL DEFAULT 'Pending'` and `reminder_dismissed TINYINT(1) NOT NULL DEFAULT 0`. |
| `sprint2_upgrade.sql` | **New** | Upgrades a Sprint 1 database in place. Adds each column only if missing (re-runnable), never drops/deletes. Works on MariaDB and MySQL (no DELIMITER). |
| `event-attendees.php` | Modified | PB-09 form (Pending/Present/Absent per student), Save Attendance, CSRF, ownership re-check, ID validation, transaction, UPDATE only, counters, PRG redirect. PB-08 content preserved. |
| `header.php` | Modified | Loads reminders for students, reminder banner, count badge on "My Dashboard". |
| `dismiss-reminder.php` | **New** | POST-only "Mark as read" (student role, CSRF, ownership, UPDATE `reminder_dismissed = 1`, safe redirect). |
| `student-dashboard.php` | Modified | Full PB-11 page (clubs, events, Upcoming/Past, attendance badges, stats, empty states, reminders section). |
| `event.php` | Modified (bug fix) | Registration blocked for events that already started (server-side + disabled button). |
| `assets/style.css` | Modified (appended) | Status badges, attendance segmented control, reminder cards, dashboard layout, mobile rules. |
| `README.txt` | Modified | Sprint 2 setup (new install vs upgrade), time zone, test data, CLI checks. |
| `sprint2_test_data.sql` | **New** (optional) | Test accounts, 2 clubs, `[TEST]` events at +12 h / +48 h / −3 h / +6 h (dismissed) / full. |
| `tests/sprint2_checks.php` | **New** (optional) | Read-only CLI checks: PHP vs MySQL time, columns, duplicates, 4 reminder cases. Refuses to run in a browser. |
| `tests/harness_results.txt` | **New** | Output of the 69 automated end-to-end checks run while building (see §4). |
| `SPRINT2_DELIVERY.md` | **New** | This report. |

Unchanged: `index.php`, `login.php`, `logout.php`, `register.php`, `dashboard.php`, `manage-club.php`, `clubs.php`, `club.php`, `events.php`, `footer.php`.

---

## 3. Traceability: Product Backlog item → acceptance criteria → implementation

| PBI | Acceptance criterion | Implemented in | Verified (harness §4) |
|---|---|---|---|
| **PB-08** View registered students | Leader opens list only for own club's event; others get *Access denied* | `event-attendees.php` (query `e.id = ? AND c.leader_id = ?`, 403) | ✅ other leader 403, student 403 |
| | Full name, university email, registration time | `event-attendees.php` table | ✅ |
| | Registered count vs capacity | `capacity-badge` "x / capacity Registered" | ✅ |
| | Message when no registrations | `.empty` "No students registered for this event yet." | ✅ |
| | Reachable from club management | `manage-club.php` → "View Attendees" | ✅ |
| **PB-09** Digital attendance | DB columns (Task 9.1) | `database.sql`, `sprint2_upgrade.sql` | ⚠️ SQL not executed on MySQL here |
| | Present / Absent per student, Pending kept/allowed | radio group per row, Pending selectable | ✅ incl. back to Pending |
| | One "Save Attendance" for all rows | single form, single POST | ✅ |
| | UPDATE not INSERT; PDO prepared statements | `UPDATE registrations SET attendance_status = ? WHERE id = ? AND event_id = ?` | ✅ row count unchanged |
| | CSRF | `verify_csrf()` | ✅ 419 without token |
| | Ownership checked on save, not only on view | ownership query runs before POST handling | ✅ other leader POST → 403, no change |
| | All registration IDs belong to this event | `SELECT id … WHERE event_id = ? FOR UPDATE` + `array_diff` | ✅ mixed foreign ID → nothing saved |
| | Transaction | `beginTransaction` / `commit` / `rollBack` | ✅ rollback verified |
| | Success message | "Attendance saved for N students: x Present, y Absent, z Pending." | ✅ |
| | Present/Absent/Pending counters, updated after save, kept after refresh | counters computed from DB on every load; Post/Redirect/Get | ✅ |
| | No duplicate attendance records | attendance lives on the `registrations` row; `UNIQUE (user_id, event_id)` | ✅ |
| **PB-10** Reminders | Asia/Riyadh for PHP and MySQL | `config.php` | ✅ PHP side; ⚠️ MySQL side needs `tests/sprint2_checks.php` on XAMPP |
| | `get_upcoming_reminders($user_id)`: own registrations, start in (now, now+24h], not dismissed, nearest first | `helpers.php` | ✅ 12 h shown; 48 h, ended, dismissed, other student's hidden; order checked |
| | Shown in `header.php` and `student-dashboard.php` | banner on all pages; dashboard section | ✅ |
| | Title, club, venue, date/time, remaining time, Mark as read | `render_reminders()` | ✅ |
| | No duplicate when header + dashboard | header banner skipped on dashboard + static guard | ✅ exactly one card |
| | `dismiss-reminder.php`: login + student, CSRF, ownership, UPDATE to 1, gone after refresh | `dismiss-reminder.php` | ✅ (also: GET → 405, leader → 403, open redirect blocked) |
| | Test method for 12 h / 48 h / ended / dismissed / time match | `sprint2_test_data.sql` + `tests/sprint2_checks.php` + §5 | ✅ logic; ⚠️ run on XAMPP |
| **PB-11** Student dashboard | `require_student()` | `student-dashboard.php` | ✅ leader 403, guest → login |
| | Clubs from memberships + clubs; events from registrations + events + clubs | two prepared queries | ✅ |
| | Ordered by date; Upcoming / Past | Upcoming (soonest first), Past (most recent first) | ✅ |
| | Attendance Present / Absent / Pending | colored badges | ✅ |
| | Two sections, 2 columns desktop / 1 column mobile | `.student-grid` + media query | ✅ screenshots 1280 px and 390 px |
| | Colored badges, club/event links, empty states, same visual identity | CSS appended with existing variables | ✅ |

---

## 4. Testing actually performed in this delivery (honest status)

Environment available while building: **PHP 8.3 CLI only — no MySQL/MariaDB server and no XAMPP.**

| Check | Result |
|---|---|
| `php -l` on every PHP file | ✅ no syntax errors |
| 69 end-to-end HTTP checks (PB-01 → PB-11, CSRF, XSS, ownership, SQL-injection-style input, open redirect) against the real pages on PHP's built-in server | ✅ 69/69 — see `tests/harness_results.txt` |
| Same run: PHP warnings/notices in server log | 0 |
| Desktop (1280 px) and mobile (390 px) screenshots of dashboard, reminders, attendance | Checked visually; attendance table turns into cards on mobile |

**Limitation:** in that harness only `config.php` was replaced by an SQLite connection that translates `NOW()`, `DATE_ADD(… INTERVAL n HOUR)`, `INSERT IGNORE` and strips `FOR UPDATE`. Therefore the following were **NOT** executed and must be tested on XAMPP:
- `database.sql`, `sprint2_upgrade.sql`, `sprint2_test_data.sql` on MySQL/MariaDB.
- `SET time_zone = '+03:00'` and real MySQL `NOW()` vs PHP time (run `tests/sprint2_checks.php`).
- Row locking (`FOR UPDATE`) under real InnoDB.
- `tests/sprint2_checks.php` itself (uses `information_schema`, MySQL only).

---

## 5. Step-by-step test on XAMPP (with test data)

**Preparation**
1. Back up the DB (phpMyAdmin → Export). Import `sprint2_upgrade.sql` (existing DB) **or** `database.sql` (new DB).
   Expected: last result lists `attendance_status` and `reminder_dismissed`; registrations count unchanged. Import it a second time → "already exists – skipped", no error.
2. Import `sprint2_test_data.sql`. Expected last result: one row, `[TEST] Starts in 12 hours`.
3. Run `C:\xampp\php\php.exe C:\xampp\htdocs\CampusConnect\tests\sprint2_checks.php` → all `[PASS]` (time zone, columns, 12 h / 48 h / ended / dismissed).

All test passwords: `Test@1234`.

| # | Login as | Steps | Expected |
|---|---|---|---|
| T1 | test.student1 | Open any page (e.g. Events) | Banner "EVENT REMINDERS – 1 event" with *[TEST] Starts in 12 hours*, TEST Club A, venue, date, "Starts in 11 h …", Mark as read. Nav shows "My Dashboard 1". 48 h, ended and 6 h (dismissed) events **not** shown. |
| T2 | test.student1 | Open My Dashboard | Reminder appears **once** (in the dashboard, no banner above). Joined clubs: TEST Club A. Upcoming: 12 h, 48 h, 6 h events (date order). Past: "Ended 3 hours ago". Badges Upcoming/Past + Pending. |
| T3 | test.student1 | Click Mark as read → refresh (F5) | "Reminder marked as read." Banner and nav count disappear and stay hidden after refresh. phpMyAdmin: that row `reminder_dismissed = 1`. |
| T4 | test.student2 | My Dashboard | Clubs: empty state "You have not joined any clubs yet." Events listed. Reminders: 12 h event only (her own). Student1's dismissal did not affect her. |
| T5 | test.student1 | Open `event.php` of "[TEST] Full event (capacity 1)" → register | "Sorry, this event is full." (PB-07) |
| T6 | test.student1 | Open the "Ended 3 hours ago" event page | Button "Registration closed" (disabled). |
| T7 | test.leader1 | Dashboard → TEST Club A → Manage → "View Attendees" on *Ended 3 hours ago* | 2 students (name, email, registered at), "2 / 30 Registered", Pending: 2. |
| T8 | test.leader1 | Set Student One = Present, Student Two = Absent → Save Attendance → F5 | "Attendance saved for 2 students: 1 Present, 1 Absent, 0 Pending." Counters Present 1 / Absent 1 / Pending 0, still the same after refresh. Registrations row count unchanged. |
| T9 | test.student1 | My Dashboard | "Ended 3 hours ago" shows **Present** badge; "Events attended" = 1. |
| T10 | test.leader1 | View Attendees on an event with no registrations (create one) | "No students registered for this event yet." and no Save button. |
| T11 | test.leader2 | Paste test.leader1's attendee URL (`event-attendees.php?id=…`) | "Access denied…" (403). |
| T12 | test.leader1 | Open `event-attendees.php?id=<id of "[TEST] Club B private list">` | Access denied (403). |
| T13 | test.leader1 | Open `student-dashboard.php` | "Access denied. Student accounts only." |
| T14 | test.student1 | Open `event-attendees.php?id=…` | "Access denied. Club leaders only." |
| T15 | any | DevTools → delete the hidden `csrf` input → submit Save Attendance / Mark as read | "Invalid request…" (419), nothing changes. |
| T16 | test.leader1 | DevTools → change a radio `name` to `attendance[<registration id of another event>]` → Save | "…not registered for this event… Nothing was saved." |
| T17 | — | Phone width (DevTools 390 px) | Dashboard single column; attendance rows become cards; reminder button under the text. |
| T18 | — | Re-test Sprint 1: register (non-PNU email rejected), login/logout, create club, create event (past date rejected), join club (duplicate message), register (duplicate message) | Same behaviour as Sprint 1. |

---

## 6. Security review (PB-01 → PB-11)

| Area | Finding |
|---|---|
| SQL injection | Every query with user input uses PDO prepared statements; IDs cast to `int`; attendance values whitelisted against `ATTENDANCE_STATUSES`. |
| XSS | All dynamic output goes through `e()` (htmlspecialchars, ENT_QUOTES). Harness inserted `<script>` / `<img onerror>` in club, event, venue and user names — rendered as text everywhere. |
| CSRF | Global `verify_csrf()` on every POST plus explicit calls in the two new POST handlers. `dismiss-reminder.php` refuses GET. |
| Authorization / ownership | Attendee view **and** save require the event's club to belong to the logged-in leader. Dismiss requires the registration to belong to the logged-in student. Dashboard requires student role. |
| Open redirect | `return` parameter of Mark as read accepts only local `page.php?…` paths. |
| Bug fixed (Sprint 1, PB-07) | Students could register for an event that had already started/ended via its URL → now blocked server-side. |

---

## 7. Remaining issues / not implemented / to verify

1. **Not executed on real MySQL/XAMPP** (see §4): the three SQL files, the MySQL time-zone line, InnoDB locking, `tests/sprint2_checks.php`. Run §5 before the Week 14 demo.
2. **Reminders are in-site only**: they appear when the student opens the website within 24 h of the event. No e-mail/SMS/push (would need a mail server or cron job, outside Sprint 2 scope). The PB-10 story says "receive … notifications"; confirm with the Product Owner that in-site reminders meet the Definition of Done.
3. **Events have a start time only** (no end/duration column), so "Past" means *start time has passed*, and reminders stop once the event starts.
4. **Attendance can be saved before the event starts** (a note is shown). PB-09 did not ask to restrict it; restrict later if the PO wants.
5. **Old registrations after upgrade** start as `Pending` / reminder not dismissed — expected.
6. If MySQL ever runs without `SET time_zone` (e.g. editing config.php incorrectly), `registered_at` display and reminders shift by the server offset. `tests/sprint2_checks.php` detects this.
7. Pre-existing, not changed (out of Sprint 2 scope): role self-selection at registration (README already notes it), logout via GET link, no login rate-limiting, session cookie flags default.
8. Nothing was pushed to GitHub.
