<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}
csrf_verify_header();

$studentId = (int) $_SESSION['student_id'];
$input = json_decode(file_get_contents('php://input'), true);

// Clamp everything to valid ranges instead of trusting client input directly —
// this keeps bad data from ever reaching ConditionEvaluator's math.
function clampInt($value, $min, $max, $default) {
    $value = (int) ($value ?? $default);
    return max($min, min($max, $value));
}
function clampFloat($value, $min, $max, $default) {
    $value = (float) ($value ?? $default);
    return max($min, min($max, $value));
}

$moodScore      = clampInt($input['mood_score'] ?? null, 1, 5, 3);
$sleepHours     = clampFloat($input['sleep_hours'] ?? null, 0, 24, 7);
$studyHoursWeek = clampFloat($input['study_hours_week'] ?? null, 0, 100, 0);
$attendancePct  = clampFloat($input['attendance_pct'] ?? null, 0, 100, 100);
$gpaSelfReport  = clampFloat($input['gpa_self_report'] ?? null, 1.0, 5.0, 1.0);
$workloadScore  = clampInt($input['workload_score'] ?? null, 1, 5, 3);
$socialScore    = clampInt($input['social_score'] ?? null, 1, 5, 3);
$financialScore = clampInt($input['financial_stress_score'] ?? null, 1, 5, 3);

$stmt = $pdo->prepare(
    "INSERT INTO checkins
     (student_id, mood_score, sleep_hours, study_hours_week, attendance_pct,
      gpa_self_report, workload_score, social_score, financial_stress_score)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $studentId, $moodScore, $sleepHours, $studyHoursWeek, $attendancePct,
    $gpaSelfReport, $workloadScore, $socialScore, $financialScore,
]);

echo json_encode(['success' => true, 'checkin_id' => $pdo->lastInsertId()]);