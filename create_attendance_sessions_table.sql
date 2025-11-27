-- SQL to create `attendance_sessions` table
-- Run after selecting your database, e.g. USE apw_project;

CREATE TABLE IF NOT EXISTS `attendance_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `opened_by` INT NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
