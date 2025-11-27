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
if ($session_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid session_id']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE attendance_sessions SET status = :status WHERE id = :id');
    $stmt->execute([':status' => 'closed', ':id' => $session_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'No session updated (id may not exist)']);
    } else {
        echo json_encode(['success' => true, 'closed' => $session_id]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
