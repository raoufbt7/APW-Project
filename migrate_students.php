<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';
if (!file_exists($studentsFile)) {
    echo json_encode(['success' => false, 'error' => 'student.json not found']);
    exit;
}

$json = file_get_contents($studentsFile);
$fileStudents = json_decode($json, true);
if (!$fileStudents || !is_array($fileStudents)) {
    echo json_encode(['success' => false, 'error' => 'Invalid student.json']);
    exit;
}

$migrated = 0;
$errors = [];

try {
    foreach ($fileStudents as $s) {
        $matricule = isset($s['id']) ? trim($s['id']) : '';
        $lname = isset($s['lname']) ? trim($s['lname']) : '';
        $fname = isset($s['fname']) ? trim($s['fname']) : '';
        $fullname = $lname && $fname ? trim($lname . ' ' . $fname) : '';
        $email = isset($s['email']) ? trim($s['email']) : null;

        if (!$matricule || !$fullname) continue;

        try {
            $stmt = $pdo->prepare('INSERT IGNORE INTO students (fullname, matricule, email) VALUES (:fullname, :matricule, :email)');
            $stmt->execute([':fullname' => $fullname, ':matricule' => $matricule, ':email' => $email]);
            $migrated++;
        } catch (PDOException $e) {
            $errors[] = "Failed to migrate student $matricule: " . $e->getMessage();
        }
    }

    echo json_encode(['success' => true, 'migrated' => $migrated, 'errors' => $errors]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
