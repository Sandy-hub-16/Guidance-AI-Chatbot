<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}
$studentId = (int) $_SESSION['student_id'];

$stmt = $pdo->prepare(
    "SELECT c.conversation_id, c.started_at,
            (SELECT message_text FROM messages m
             WHERE m.conversation_id = c.conversation_id AND m.sender = 'student'
             ORDER BY m.created_at ASC LIMIT 1) AS preview
     FROM conversations c
     WHERE c.student_id = ?
     ORDER BY c.started_at DESC"
);
$stmt->execute([$studentId]);

echo json_encode($stmt->fetchAll());