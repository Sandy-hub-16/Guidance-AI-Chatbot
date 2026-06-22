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
$conversationId = (int) ($_GET['conversation_id'] ?? 0);

// Ownership check — without this, a student could read another student's
// conversation just by guessing/incrementing the conversation_id in the URL.
$stmt = $pdo->prepare("SELECT conversation_id FROM conversations WHERE conversation_id = ? AND student_id = ?");
$stmt->execute([$conversationId, $studentId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Conversation not found.']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT message_id, sender, message_text, flagged_crisis, recommended_rec_ids, created_at
     FROM messages WHERE conversation_id = ? ORDER BY created_at ASC"
);
$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

// Resolve recommended_rec_ids into full recommendation objects
$recIds = [];
foreach ($messages as $m) {
    if (!empty($m['recommended_rec_ids'])) {
        $recIds = array_merge($recIds, explode(',', $m['recommended_rec_ids']));
    }
}
$recIds = array_unique(array_filter($recIds));

$recsById = [];
if (!empty($recIds)) {
    $placeholders = implode(',', array_fill(0, count($recIds), '?'));
    $stmt = $pdo->prepare("SELECT rec_id, activity_text, activity_type FROM recommendations WHERE rec_id IN ($placeholders)");
    $stmt->execute(array_values($recIds));
    foreach ($stmt->fetchAll() as $rec) {
        $recsById[$rec['rec_id']] = $rec;
    }
}

foreach ($messages as &$m) {
    $m['recommendations'] = [];
    if (!empty($m['recommended_rec_ids'])) {
        foreach (explode(',', $m['recommended_rec_ids']) as $id) {
            if (isset($recsById[$id])) $m['recommendations'][] = $recsById[$id];
        }
    }
}

echo json_encode($messages);