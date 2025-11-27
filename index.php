<?php
// index.php - Unified Student Management and Attendance
$date = date('Y-m-d');
$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';
$attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_{$date}.json";

// Navigation logic
$page = $_GET['page'] ?? 'add_student';

// --- Add Student Logic ---
$student_id = $name = $group = '';
$add_errors = [];
$add_confirmation = '';
if ($page === 'add_student' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $group = trim($_POST['group'] ?? '');
    if ($student_id === '') $add_errors[] = 'Student ID is required.';
    if ($name === '') $add_errors[] = 'Name is required.';
    if ($group === '') $add_errors[] = 'Group is required.';
    if (empty($add_errors)) {
        $students = [];
        if (file_exists($studentsFile)) {
            $json = file_get_contents($studentsFile);
            $students = json_decode($json, true) ?: [];
        }
        $students[] = [
            'student_id' => $student_id,
            'name' => $name,
            'group' => $group
        ];
        file_put_contents($studentsFile, json_encode($students, JSON_PRETTY_PRINT), LOCK_EX);
        $add_confirmation = 'Student added successfully!';
        $student_id = $name = $group = '';
    }
}

// --- Attendance Logic ---
$attendanceTaken = false;
$attendanceMessage = '';
$students = [];
if (file_exists($studentsFile)) {
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
}
if ($page === 'attendance' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($attendanceFile)) {
        $attendanceTaken = true;
        $attendanceMessage = 'Attendance for today has already been taken.';
    } else {
        $attendance = [];
        foreach ($students as $student) {
            $sid = $student['student_id'];
            $status = $_POST['attendance'][$sid] ?? 'absent';
            $attendance[] = [
                'student_id' => $sid,
                'status' => $status
            ];
        }
        file_put_contents($attendanceFile, json_encode($attendance, JSON_PRETTY_PRINT), LOCK_EX);
        $attendanceMessage = 'Attendance saved successfully!';
        $attendanceTaken = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management & Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        nav { margin-bottom: 2em; }
        nav a { margin-right: 1em; text-decoration: none; color: #0074d9; }
        nav a.selected { font-weight: bold; color: #111; }
        .error { color: red; }
        .success { color: green; }
        form { max-width: 400px; }
        label { display: block; margin-top: 1em; }
        input[type="text"] { width: 100%; padding: 0.5em; }
        input[type="submit"] { margin-top: 1em; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; margin-top: 2em; }
        th, td { border: 1px solid #ccc; padding: 0.5em; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <nav>
        <a href="?page=add_student" class="<?= $page === 'add_student' ? 'selected' : '' ?>">Add Student</a>
        <a href="?page=attendance" class="<?= $page === 'attendance' ? 'selected' : '' ?>">Take Attendance</a>
    </nav>
    <?php if ($page === 'add_student'): ?>
        <h1>Add Student</h1>
        <?php if (!empty($add_errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($add_errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($add_confirmation): ?>
            <div class="success">
                <?= htmlspecialchars($add_confirmation) ?>
            </div>
        <?php endif; ?>
        <form method="post" action="?page=add_student">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>">
            <label for="group">Group:</label>
            <input type="text" id="group" name="group" value="<?= htmlspecialchars($group) ?>">
            <input type="submit" value="Add Student">
        </form>
    <?php elseif ($page === 'attendance'): ?>
        <h1>Take Attendance (<?= htmlspecialchars($date) ?>)</h1>
        <?php if ($attendanceMessage): ?>
            <div class="<?= $attendanceTaken ? 'error' : 'success' ?>">
                <?= htmlspecialchars($attendanceMessage) ?>
            </div>
        <?php endif; ?>
        <?php if (!$attendanceTaken): ?>
            <?php if (empty($students)): ?>
                <p>No students found. Please add students first.</p>
            <?php else: ?>
            <form method="post" action="?page=attendance">
                <table>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['group']) ?></td>
                        <td>
                            <label>
                                <input type="radio" name="attendance[<?= htmlspecialchars($student['student_id']) ?>]" value="present" checked> Present
                            </label>
                            <label>
                                <input type="radio" name="attendance[<?= htmlspecialchars($student['student_id']) ?>]" value="absent"> Absent
                            </label>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <input type="submit" value="Save Attendance">
            </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
