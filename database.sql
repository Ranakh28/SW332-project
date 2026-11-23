CREATE DATABASE IF NOT EXISTS campusconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusconnect;
-- Fresh install (Sprint 1 + Sprint 2). If you already have the Sprint 1 database, import sprint2_upgrade.sql instead.
CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('student','club_leader') NOT NULL DEFAULT 'student',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS clubs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 leader_id INT NOT NULL,
 name VARCHAR(150) NOT NULL,
 category VARCHAR(100) DEFAULT NULL,
 description TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_club_leader FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS memberships (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 club_id INT NOT NULL,
 joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_membership (user_id, club_id),
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS events (
 id INT AUTO_INCREMENT PRIMARY KEY,
 club_id INT NOT NULL,
 title VARCHAR(180) NOT NULL,
 description TEXT,
 venue VARCHAR(180) NOT NULL,
 event_date DATETIME NOT NULL,
 capacity INT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
 INDEX idx_event_date (event_date)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS registrations (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 event_id INT NOT NULL,
 registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 -- Sprint 2 - PB-09: digital attendance (one row per student per event, so no duplicate attendance)
 attendance_status ENUM('Pending','Present','Absent') NOT NULL DEFAULT 'Pending',
 -- Sprint 2 - PB-10: 1 = the student clicked "Mark as read" on the 24-hour reminder
 reminder_dismissed TINYINT(1) NOT NULL DEFAULT 0,
 UNIQUE KEY unique_registration (user_id, event_id),
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- After registering a club leader account through the website, promote it in phpMyAdmin:
-- UPDATE users SET role='club_leader' WHERE email='leader@pnu.edu.sa';
