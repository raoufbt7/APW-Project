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

    echo "Database and table created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
