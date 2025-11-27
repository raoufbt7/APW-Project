<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) $data = $_POST;

$session_id = isset($data['session_id']) ? (int)$data['session_id'] : 0;
$course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
$opened_by = isset($data['opened_by']) ? (int)$data['opened_by'] : 0;
$date = isset($data['date']) && $data['date'] !== '' ? $data['date'] : null;
$status = isset($data['status']) ? $data['status'] : 'open';

if ($session_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid session_id']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE attendance_sessions SET course_id = :course_id, group_id = :group_id, date = :date, opened_by = :opened_by, status = :status WHERE id = :session_id');
    $stmt->execute([
        ':course_id' => $course_id,
        ':group_id' => $group_id,
        ':date' => $date,
        ':opened_by' => $opened_by,
        ':status' => $status,
        ':session_id' => $session_id
    ]);
    echo json_encode(['success' => true, 'message' => 'Session updated']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
