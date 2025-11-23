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

$fullname = isset($data['fullname']) ? trim($data['fullname']) : '';
$matricule = isset($data['matricule']) ? trim($data['matricule']) : '';
$group_id = isset($data['group_id']) && $data['group_id'] !== '' ? (int)$data['group_id'] : null;

if ($fullname === '' || $matricule === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: fullname and matricule']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO students (fullname, matricule, group_id) VALUES (:fullname, :matricule, :group_id)');
    $stmt->execute([':fullname' => $fullname, ':matricule' => $matricule, ':group_id' => $group_id]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'student' => ['id' => $id, 'fullname' => $fullname, 'matricule' => $matricule, 'group_id' => $group_id]]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Matricule already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
