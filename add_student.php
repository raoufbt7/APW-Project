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

// support both new and legacy payloads
$fullname = '';
if (isset($data['fullname'])) {
    $fullname = trim($data['fullname']);
} else {
    $lname = isset($data['lname']) ? trim($data['lname']) : '';
    $fname = isset($data['fname']) ? trim($data['fname']) : '';
    if ($lname || $fname) $fullname = trim($lname . ' ' . $fname);
}
$matricule = '';
if (isset($data['matricule'])) $matricule = trim($data['matricule']);
elseif (isset($data['id'])) $matricule = trim($data['id']);
$email = isset($data['email']) ? trim($data['email']) : null;
$group_id = isset($data['group_id']) && $data['group_id'] !== '' ? (int)$data['group_id'] : null;

if ($fullname === '' || $matricule === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: fullname and matricule']);
    exit;
}

try {
    // include email if provided
    if ($email !== null) {
        $stmt = $pdo->prepare('INSERT INTO students (fullname, matricule, email, group_id) VALUES (:fullname, :matricule, :email, :group_id)');
        $stmt->execute([':fullname' => $fullname, ':matricule' => $matricule, ':email' => $email, ':group_id' => $group_id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO students (fullname, matricule, group_id) VALUES (:fullname, :matricule, :group_id)');
        $stmt->execute([':fullname' => $fullname, ':matricule' => $matricule, ':group_id' => $group_id]);
    }
    $id = (int)$pdo->lastInsertId();
        $student = ['id' => $id, 'fullname' => $fullname, 'matricule' => $matricule, 'email' => $email, 'group_id' => $group_id];

        // Also append to student.json for compatibility with file-based workflows
        $studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';
        if (is_writable(__DIR__) || is_writable($studentsFile) || !file_exists($studentsFile)) {
            $fileStudents = [];
            if (file_exists($studentsFile)) {
                $json = file_get_contents($studentsFile);
                $fileStudents = json_decode($json, true) ?: [];
            }
            // avoid duplicate matricule entries in the file
            $exists = false;
            foreach ($fileStudents as $fs) {
                if (isset($fs['matricule']) && $fs['matricule'] == $matricule) { $exists = true; break; }
            }
            if (!$exists) {
                $fileStudents[] = ['id' => (string)$matricule, 'lname' => ($lname ?? ''), 'fname' => ($fname ?? ''), 'email' => $email, 'matricule' => $matricule];
                @file_put_contents($studentsFile, json_encode($fileStudents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            }
        }

        echo json_encode(['success' => true, 'student' => $student]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Matricule already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
