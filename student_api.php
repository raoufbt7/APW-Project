<?php
header('Content-Type: application/json; charset=utf-8');
$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!file_exists($studentsFile)) {
        echo json_encode(['success' => true, 'students' => []]);
        exit;
    }
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
    echo json_encode(['success' => true, 'students' => $students]);
    exit;
}

if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) $data = $_POST;

    $id = isset($data['id']) ? trim($data['id']) : (isset($data['student_id']) ? trim($data['student_id']) : '');
    $lname = isset($data['lname']) ? trim($data['lname']) : (isset($data['lastName']) ? trim($data['lastName']) : '');
    $fname = isset($data['fname']) ? trim($data['fname']) : (isset($data['firstName']) ? trim($data['firstName']) : '');
    $email = isset($data['email']) ? trim($data['email']) : '';

    if ($id === '' || $lname === '' || $fname === '') {
        echo json_encode(['success' => false, 'error' => 'Missing required fields: id, lname, fname']);
        exit;
    }

    $students = [];
    if (file_exists($studentsFile)) {
        $json = file_get_contents($studentsFile);
        $students = json_decode($json, true) ?: [];
    }

    foreach ($students as $s) {
        if ((string)$s['id'] === (string)$id) {
            echo json_encode(['success' => false, 'error' => 'Student exists']);
            exit;
        }
    }

    $students[] = ['id' => $id, 'lname' => $lname, 'fname' => $fname, 'email' => $email];

    $written = file_put_contents($studentsFile, json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($written === false) {
        echo json_encode(['success' => false, 'error' => 'Could not write students file']);
        exit;
    }

    echo json_encode(['success' => true, 'student' => end($students), 'students' => $students]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unsupported method']);
