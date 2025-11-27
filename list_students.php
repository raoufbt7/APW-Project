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
    $stmt = $pdo->query('SELECT id, fullname, matricule, email, group_id FROM students ORDER BY id ASC');
    $students = $stmt->fetchAll();

    // For compatibility with the front-end which expects lname/fname, split fullname when present
    foreach ($students as &$s) {
        if (isset($s['fullname']) && $s['fullname'] !== null && trim($s['fullname']) !== '') {
            $parts = preg_split('/\s+/', trim($s['fullname']));
            $s['lname'] = $parts[0] ?? '';
            $s['fname'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
        } else {
            $s['lname'] = $s['lname'] ?? '';
            $s['fname'] = $s['fname'] ?? '';
        }
        // ensure keys present
        $s['email'] = $s['email'] ?? null;
        $s['matricule'] = $s['matricule'] ?? null;
        $s['group_id'] = $s['group_id'] ?? null;
    }

    echo json_encode(['success' => true, 'students' => $students]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
