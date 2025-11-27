<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!isset($_GET['session_id'])) {
        echo json_encode(['success' => false, 'error' => 'Session ID required']);
        exit;
    }
    $session_id = intval($_GET['session_id']);
    $attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_session_{$session_id}.json";
    if (file_exists($attendanceFile)) {
        $attendance = json_decode(file_get_contents($attendanceFile), true);
        echo json_encode(['success' => true, 'attendance' => $attendance ?: []]);
    } else {
        echo json_encode(['success' => true, 'attendance' => []]);
    }
} elseif ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }

    // Handle batch attendance save (from form)
    if (isset($data['attendance']) && is_array($data['attendance'])) {
        // For batch save, assume it's for a new session or update existing
        // Since no session_id is provided, we need to create a new session or use a default
        // For now, let's assume session_id is 1 or something, but better to require it
        // Actually, the form should include session_id if it's for an existing session
        // But for initial take attendance, perhaps create a new session
        // To fix, let's add session_id to the form submission
        if (!isset($data['session_id'])) {
            echo json_encode(['success' => false, 'error' => 'Missing session_id for batch save']);
            exit;
        }
        $session_id = intval($data['session_id']);
        $attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_session_{$session_id}.json";
        $attendance = [];
        foreach ($data['attendance'] as $att) {
            $student_id = intval($att['student_id']);
            $status = $att['status']; // 'present' or 'absent'
            $presence = ($status === 'present') ? 1 : 0;
            $participation = 0; // Default, can be updated later
            $attendance[] = ['student_id' => $student_id, 'presence' => $presence, 'participation' => $participation];
        }
        $written = file_put_contents($attendanceFile, json_encode($attendance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($written === false) {
            echo json_encode(['success' => false, 'error' => 'Could not write attendance file']);
        } else {
            echo json_encode(['success' => true, 'file' => basename($attendanceFile)]);
        }
        exit;
    }

    // Handle individual attendance update
    if (!isset($data['session_id']) || !isset($data['student_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing session_id or student_id']);
        exit;
    }

    $session_id = intval($data['session_id']);
    $student_id = intval($data['student_id']);
    $presence = isset($data['presence']) ? intval($data['presence']) : 0;
    $participation = isset($data['participation']) ? intval($data['participation']) : 0;

    $attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_session_{$session_id}.json";

    // Load existing attendance
    $attendance = [];
    if (file_exists($attendanceFile)) {
        $attendance = json_decode(file_get_contents($attendanceFile), true) ?: [];
    }

    // Find or add student attendance
    $found = false;
    foreach ($attendance as &$att) {
        if ($att['student_id'] == $student_id) {
            $att['presence'] = $presence;
            $att['participation'] = $participation;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $attendance[] = ['student_id' => $student_id, 'presence' => $presence, 'participation' => $participation];
    }

    $written = file_put_contents($attendanceFile, json_encode($attendance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($written === false) {
        echo json_encode(['success' => false, 'error' => 'Could not write attendance file']);
    } else {
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
