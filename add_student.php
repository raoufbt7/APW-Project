<?php
header('Content-Type: application/json; charset=utf-8');

$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) $data = $_POST;

// Support both new and legacy payloads
$lname = isset($data['lname']) ? trim($data['lname']) : '';
$fname = isset($data['fname']) ? trim($data['fname']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$id = isset($data['id']) ? trim($data['id']) : (isset($data['student_id']) ? trim($data['student_id']) : '');

if ($id === '' || $lname === '' || $fname === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields: id, lname, fname']);
    exit;
}

// Load existing students
$students = [];
if (file_exists($studentsFile)) {
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
}

// Check if student ID already exists
foreach ($students as $student) {
    if ((string)$student['id'] === (string)$id) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Student ID already exists']);
        exit;
    }
}

// Add new student
$newStudent = [
    'id' => $id,
    'lname' => $lname,
    'fname' => $fname,
    'email' => $email
];
$students[] = $newStudent;

// Save to file
$written = file_put_contents($studentsFile, json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not write students file']);
    exit;
}

echo json_encode(['success' => true, 'student' => $newStudent, 'students' => $students]);
