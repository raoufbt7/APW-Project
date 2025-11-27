<?php
header('Content-Type: application/json; charset=utf-8');

$sessionsFile = __DIR__ . DIRECTORY_SEPARATOR . 'sessions.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) $data = $_POST;

$session_id = isset($data['session_id']) ? (int)$data['session_id'] : 0;
if ($session_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid session_id']);
    exit;
}

$sessions = [];
if (file_exists($sessionsFile)) {
    $json = file_get_contents($sessionsFile);
    $sessions = json_decode($json, true) ?: [];
}

$found = false;
foreach ($sessions as &$session) {
    if ($session['id'] == $session_id) {
        $session['status'] = 'closed';
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Session not found']);
    exit;
}

$written = file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not write sessions file']);
    exit;
}

echo json_encode(['success' => true, 'closed' => $session_id]);
