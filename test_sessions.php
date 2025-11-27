<?php
/**
 * Test script to insert 2-3 attendance sessions for manual testing.
 * Run via CLI: php test_sessions.php
 * Or open in browser: http://localhost/APW_Project/test_sessions.php
 */
require __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if (!$pdo) {
    echo "DB connection failed\n";
    exit(1);
}

$samples = [
    ['course_id' => 1, 'group_id' => 1, 'opened_by' => 10, 'date' => date('Y-m-d')],
    ['course_id' => 2, 'group_id' => 1, 'opened_by' => 11, 'date' => date('Y-m-d', strtotime('-1 day'))],
    ['course_id' => 1, 'group_id' => 2, 'opened_by' => 10, 'date' => date('Y-m-d', strtotime('-2 days'))],
];

foreach ($samples as $s) {
    try {
        $stmt = $pdo->prepare('INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (:course_id, :group_id, :date, :opened_by, :status)');
        $stmt->execute([
            ':course_id' => $s['course_id'],
            ':group_id' => $s['group_id'],
            ':date' => $s['date'],
            ':opened_by' => $s['opened_by'],
            ':status' => 'open'
        ]);
        $id = $pdo->lastInsertId();
        echo "Inserted session id: $id (course {$s['course_id']}, group {$s['group_id']}, date {$s['date']})\n";
    } catch (PDOException $e) {
        echo "Error inserting sample: " . $e->getMessage() . "\n";
    }
}
