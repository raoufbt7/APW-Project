<?php
$studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'students.json';
// Fallback to `student.json` if `students.json` is not present
if (!file_exists($studentsFile)) {
    $studentsFile = __DIR__ . DIRECTORY_SEPARATOR . 'student.json';
}
$date = date('Y-m-d');
$attendanceFile = __DIR__ . DIRECTORY_SEPARATOR . "attendance_{$date}.json";

$students = [];
$studentsSource = basename($studentsFile);
if (file_exists($studentsFile)) {
    $json = file_get_contents($studentsFile);
    $students = json_decode($json, true) ?: [];
}

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($attendanceFile)) {
        $error = 'Attendance for today has already been taken';
    } else {
        $posted = $_POST['attendance'] ?? [];
        if (!is_array($posted)) $posted = [];

        $out = [];
        foreach ($students as $s) {
            $id = isset($s['id']) ? (string)$s['id'] : (isset($s['student_id']) ? (string)$s['student_id'] : '');
            if ($id === '') continue;
            $status = isset($posted[$id]) ? $posted[$id] : 'absent';
            $status = ($status === 'present') ? 'present' : 'absent';
            $out[] = ['student_id' => $id, 'status' => $status];
        }

        $written = file_put_contents($attendanceFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        if ($written === false) {
            $error = 'Could not write attendance file';
        } else {
            $message = 'Attendance saved to ' . basename($attendanceFile);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Take Attendance - <?= htmlspecialchars($date) ?></title>
    <style>
        body{ font-family: Arial, sans-serif; margin:20px; }
        table{ border-collapse: collapse; width:100%; max-width:800px; }
        th, td{ border:1px solid #ccc; padding:8px; text-align:left; }
        th{ background:#f0f0f0; }
        .msg{ color:green; font-weight:bold; }
        .err{ color:red; font-weight:bold; }
        input[type=submit]{ padding:8px 14px; background:#333; color:#fff; border:none; border-radius:6px; }
    </style>
</head>
<body>
    <h1>Take Attendance (<?= htmlspecialchars($date) ?>)</h1>
    <?php if ($message): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <p>No students found in <code><?= htmlspecialchars($studentsSource) ?></code>. Add students first.</p>
    <?php elseif ($error === '' && $message === ''): ?>
        <form method="post" action="">
            <table>
                <tr><th>Student ID</th><th>Name</th><th>Present</th><th>Absent</th></tr>
                <?php foreach ($students as $s):
                    $id = isset($s['id']) ? (string)$s['id'] : (isset($s['student_id']) ? (string)$s['student_id'] : '');
                    $lname = isset($s['lname']) ? $s['lname'] : (isset($s['name']) ? $s['name'] : '');
                    $fname = isset($s['fname']) ? $s['fname'] : '';
                    if ($id === '') continue;
                ?>
                <tr>
                    <td><?= htmlspecialchars($id) ?></td>
                    <td><?= htmlspecialchars(trim($lname . ' ' . $fname)) ?></td>
                    <td style="text-align:center;"><label><input type="radio" name="attendance[<?= htmlspecialchars($id) ?>]" value="present" checked> </label></td>
                    <td style="text-align:center;"><label><input type="radio" name="attendance[<?= htmlspecialchars($id) ?>]" value="absent"> </label></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p><input type="submit" value="Save Attendance"></p>
        </form>
    <?php endif; ?>

</body>
</html>
