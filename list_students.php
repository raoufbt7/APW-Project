<?php
header('Content-Type: application/json; charset=utf-8');

$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';

// Load students from JSON file
$students = [];
if (file_exists($studentsFile)) {
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
}

// Ensure all students have required fields for compatibility
foreach ($students as &$student) {
    $student['id'] = $student['id'] ?? '';
    $student['lname'] = $student['lname'] ?? '';
    $student['fname'] = $student['fname'] ?? '';
    $student['email'] = $student['email'] ?? '';
    // Add matricule for compatibility (same as id)
    $student['matricule'] = $student['id'];
    // Add fullname for compatibility
    $student['fullname'] = trim($student['lname'] . ' ' . $student['fname']);
    // Add group_id (null for now)
    $student['group_id'] = null;
}

echo json_encode(['success' => true, 'students' => $students]);
