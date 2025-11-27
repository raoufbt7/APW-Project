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
    $stmt = $pdo->prepare('DELETE FROM attendance_sessions');
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'All sessions deleted']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
