<?php
header('Content-Type: application/json; charset=utf-8');

$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($id === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing student ID']);
    exit;
}

$students = [];
if (file_exists($studentsFile)) {
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
}

$newStudents = [];
$found = false;
foreach ($students as $student) {
    if ((string)$student['id'] !== (string)$id) {
        $newStudents[] = $student;
    } else {
        $found = true;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Student not found']);
    exit;
}

$written = file_put_contents($studentsFile, json_encode($newStudents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not write students file']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
