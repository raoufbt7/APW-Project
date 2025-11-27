<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT id, course_id, group_id, date, opened_by, status FROM attendance_sessions ORDER BY date DESC, id DESC');
    $sessions = $stmt->fetchAll();
    echo json_encode(['success' => true, 'sessions' => $sessions]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
