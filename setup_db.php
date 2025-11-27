<?php
$config = require __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host={$config['db_host']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Select the database
    $pdo->exec("USE `{$config['db_name']}`");

    // Create students table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `fullname` VARCHAR(255) NOT NULL,
          `matricule` VARCHAR(100) NOT NULL,
          `email` VARCHAR(255) NULL,
          `group_id` INT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_matricule` (`matricule`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create attendance_sessions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `attendance_sessions` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `course_id` INT NOT NULL,
          `group_id` INT NOT NULL,
          `date` DATE NOT NULL,
          `opened_by` INT NOT NULL,
          `status` VARCHAR(20) NOT NULL DEFAULT 'open',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Create attendance table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `attendance` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `student_id` INT UNSIGNED NOT NULL,
          `session_id` INT UNSIGNED NOT NULL,
          `presence` TINYINT(1) NOT NULL DEFAULT 0,
          `participation` TINYINT(1) NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_student_session` (`student_id`, `session_id`),
          FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
          FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "Database and tables created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
