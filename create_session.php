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

$course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
$opened_by = isset($data['opened_by']) ? (int)$data['opened_by'] : 0;
$date = isset($data['date']) && $data['date'] !== '' ? $data['date'] : date('Y-m-d');

if ($course_id <= 0 || $group_id <= 0 || $opened_by <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid course_id, group_id or opened_by']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (:course_id, :group_id, :date, :opened_by, :status)');
    $stmt->execute([
        ':course_id' => $course_id,
        ':group_id' => $group_id,
        ':date' => $date,
        ':opened_by' => $opened_by,
        ':status' => 'open'
    ]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'session_id' => $id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
=======
<?php
header('Content-Type: application/json; charset=utf-8');

$sessionsFile = __DIR__ . DIRECTORY_SEPARATOR . 'sessions.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) $data = $_POST;

$course_id = isset($data['course_id']) ? (int)$data['course_id'] : 0;
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : 0;
$opened_by = isset($data['opened_by']) ? (int)$data['opened_by'] : 0;
$date = isset($data['date']) && $data['date'] !== '' ? $data['date'] : date('Y-m-d');

if ($course_id <= 0 || $group_id <= 0 || $opened_by <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid course_id, group_id or opened_by']);
    exit;
}

// Load existing sessions
$sessions = [];
if (file_exists($sessionsFile)) {
    $json = file_get_contents($sessionsFile);
    $sessions = json_decode($json, true) ?: [];
}

// Generate new session ID
$maxId = 0;
foreach ($sessions as $session) {
    if ($session['id'] > $maxId) {
        $maxId = $session['id'];
    }
}
$newId = $maxId + 1;

// Create new session
$newSession = [
    'id' => $newId,
    'course_id' => $course_id,
    'group_id' => $group_id,
    'date' => $date,
    'opened_by' => $opened_by,
    'status' => 'open'
];

$sessions[] = $newSession;

// Save to file
$written = file_put_contents($sessionsFile, json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not write sessions file']);
    exit;
}

echo json_encode(['success' => true, 'session_id' => $newId]);
