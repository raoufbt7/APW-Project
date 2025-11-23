<?php
$config = require __DIR__ . '/config.php';
$logFile = __DIR__ . '/logs/db_errors.log';

/**
 * Create and return a PDO connection to the MySQL database.
 * Returns PDO on success, or false on failure.
 */
function get_db_connection()
{
    global $config, $logFile;

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
        return $pdo;
    } catch (PDOException $e) {
        $msg = sprintf("%s - DB connection failed: %s\n", date('c'), $e->getMessage());
        // attempt to create log dir and write the message (best-effort, suppress warnings)
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($logFile, $msg, FILE_APPEND | LOCK_EX);
        return false;
    }
}
