<?php
header('Content-Type: application/json; charset=utf-8');
$date = date('Y-m-d');
$attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_{$date}.json";
$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Only POST allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

if (file_exists($attendanceFile)) {
    echo json_encode(['success' => false, 'error' => 'Attendance for today has already been taken']);
    exit;
}

$attendance = $data['attendance'] ?? null;
if (!is_array($attendance)) {
    echo json_encode(['success' => false, 'error' => 'Missing attendance array']);
    exit;
}

$out = [];
foreach ($attendance as $entry) {
    // support both associative entries and arrays
    if (is_array($entry)) {
        if (isset($entry['student_id'])) {
            $id = $entry['student_id'];
            $status = isset($entry['status']) ? $entry['status'] : 'absent';
            $out[] = ['student_id' => (string)$id, 'status' => $status];
        } elseif (isset($entry[0]) && isset($entry[1])) {
            // not expected shape, skip
        }
    }
}

$written = file_put_contents($attendanceFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
if ($written === false) {
    echo json_encode(['success' => false, 'error' => 'Could not write attendance file']);
    exit;
}

echo json_encode(['success' => true, 'file' => basename($attendanceFile), 'attendance' => $out]);
