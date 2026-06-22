<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/groq.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../classes/CrisisFilter.php';
require_once __DIR__ . '/../classes/GroqClient.php';
require_once __DIR__ . '/../classes/ConditionEvaluator.php';
require_once __DIR__ . '/../classes/RecommendationEngine.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}
$studentId = (int) $_SESSION['student_id'];

csrf_verify_header();

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$conversationId = isset($input['conversation_id']) ? (int) $input['conversation_id'] : null;

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}
if (mb_strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is too long.']);
    exit;
}

if (!$conversationId) {
    $stmt = $pdo->prepare("INSERT INTO conversations (student_id) VALUES (?)");
    $stmt->execute([$studentId]);
    $conversationId = (int) $pdo->lastInsertId();
}

$crisisFilter = new CrisisFilter($pdo);

if ($crisisFilter->isCrisis($message)) {
    $safeReply = $crisisFilter->getSafeResponse();

    saveMessage($pdo, $conversationId, 'student', $message, true);
    saveMessage($pdo, $conversationId, 'bot', $safeReply, true);

    echo json_encode([
        'conversation_id' => $conversationId,
        'reply'            => $safeReply,
        'crisis'           => true,
        'recommendations'  => [],
    ]);
    exit;
}

saveMessage($pdo, $conversationId, 'student', $message, false);

$stmt = $pdo->prepare("SELECT * FROM checkins WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$studentId]);
$latestCheckin = $stmt->fetch();

$conditions = [];
$recommendations = [];
$engine = new RecommendationEngine($pdo);

if ($latestCheckin) {
    $evaluator = new ConditionEvaluator();
    $conditions = $evaluator->evaluate($latestCheckin);
    $recommendations = $engine->getRecommendations($conditions);
}

$stmt = $pdo->prepare(
    "SELECT sender, message_text FROM messages WHERE conversation_id = ? ORDER BY created_at DESC LIMIT 6"
);
$stmt->execute([$conversationId]);
$recentMessages = array_reverse($stmt->fetchAll());

$groqMessages = [['role' => 'system', 'content' => buildSystemPrompt($conditions, $recommendations)]];
foreach ($recentMessages as $m) {
    $groqMessages[] = [
        'role'    => $m['sender'] === 'student' ? 'user' : 'assistant',
        'content' => $m['message_text'],
    ];
}

$groq = new GroqClient(GROQ_API_KEY, GROQ_MODEL);

try {
    $reply = $groq->chat($groqMessages);
} catch (RuntimeException $e) {
    error_log('Groq call failed: ' . $e->getMessage());
    $reply = "Sorry, I'm having trouble responding right now. Please try again in a moment.";
}

$recIds = [];
$flatRecommendations = [];
foreach ($recommendations as $list) {
    foreach ($list as $rec) {
        $recIds[] = $rec['rec_id'];
        $flatRecommendations[] = $rec;
    }
}
saveMessage($pdo, $conversationId, 'bot', $reply, false, implode(',', $recIds));

if (!empty($conditions)) {
    $engine->logConditions($studentId, $conditions, 'checkin', $latestCheckin['checkin_id'] ?? null);
}

echo json_encode([
    'conversation_id' => $conversationId,
    'reply'            => $reply,
    'crisis'           => false,
    'recommendations'  => $flatRecommendations,
]);

function saveMessage(PDO $pdo, int $conversationId, string $sender, string $text, bool $flagged, string $recIds = ''): void
{
    $stmt = $pdo->prepare(
        "INSERT INTO messages (conversation_id, sender, message_text, flagged_crisis, recommended_rec_ids)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$conversationId, $sender, $text, $flagged ? 1 : 0, $recIds ?: null]);
}

function buildSystemPrompt(array $conditions, array $recommendations): string
{
    $base = "You are a supportive academic guidance assistant for PUP students. "
          . "Listen, ask clarifying questions, validate feelings, and offer practical next steps "
          . "the way a real campus counselor would. You are not a licensed therapist and cannot "
          . "diagnose mental health conditions. Keep replies to 3-6 sentences, warm and non-judgmental. "
          . "If recommendations are listed below, weave at most 2 into your reply naturally as "
          . "suggestions, never as a list dump. For personal topics, end with one open follow-up question.";

    if (!empty($conditions)) {
        $base .= "\n\nDetected conditions: " . json_encode($conditions);
    }
    if (!empty($recommendations)) {
        $flat = [];
        foreach ($recommendations as $list) {
            foreach ($list as $rec) $flat[] = $rec['activity_text'];
        }
        $base .= "\nSuggested resources to consider mentioning: " . json_encode($flat);
    }

    return $base;
}