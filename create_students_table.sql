-- SQL to create `students` table
-- Run this in MySQL (adjust database if needed):
-- USE your_database;

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `matricule` VARCHAR(100) NOT NULL,
  `group_id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
