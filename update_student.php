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

$id = isset($data['id']) ? (int)$data['id'] : 0;
$fullname = isset($data['fullname']) ? trim($data['fullname']) : null;
$matricule = isset($data['matricule']) ? trim($data['matricule']) : null;
$group_id = array_key_exists('group_id', $data) && $data['group_id'] !== '' ? (int)$data['group_id'] : null;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid id']);
    exit;
}

// Build SET clause dynamically
$fields = [];
$params = [':id' => $id];
if ($fullname !== null) { $fields[] = 'fullname = :fullname'; $params[':fullname'] = $fullname; }
if ($matricule !== null) { $fields[] = 'matricule = :matricule'; $params[':matricule'] = $matricule; }
if ($group_id !== null) { $fields[] = 'group_id = :group_id'; $params[':group_id'] = $group_id; }

if (empty($fields)) {
    echo json_encode(['success' => false, 'error' => 'No fields to update']);
    exit;
}

$sql = 'UPDATE students SET ' . implode(', ', $fields) . ' WHERE id = :id';
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'No student updated (id may not exist or no changes)']);
    } else {
        echo json_encode(['success' => true, 'updated' => $id]);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Matricule already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
