<?php
session_start();
header('Content-Type: application/json');

// ── CRITICAL: disable display_errors for this JSON endpoint.
// XAMPP ships with display_errors = On. Any PHP notice/warning injected
// into the response body before the JSON object will make res.json() throw
// on the client side, producing the "Something went wrong" catch-block message.
ini_set('display_errors', '0');

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

$input          = json_decode(file_get_contents('php://input'), true);
$message        = trim($input['message'] ?? '');
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

// ── BUG 2 FIX: decide isFirstMessage BEFORE we create the row or save anything.
// Previously, saveMessage() was called first, so the count was always ≥ 1
// and isFirstMessage was permanently false — the first-reply logic never ran.
$isNewConversation = ($conversationId === null);

if ($isNewConversation) {
    $stmt = $pdo->prepare("INSERT INTO conversations (student_id) VALUES (?)");
    $stmt->execute([$studentId]);
    $conversationId = (int) $pdo->lastInsertId();
    $isFirstMessage = true;    // brand-new conversation → definitely first message
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE conversation_id = ?");
    $stmt->execute([$conversationId]);
    $isFirstMessage = ((int) $stmt->fetchColumn()) === 0;
}

$crisisFilter = new CrisisFilter($pdo);

if ($crisisFilter->isCrisis($message)) {
    $safeReply = $crisisFilter->getSafeResponse();

    saveMessage($pdo, $conversationId, 'student', $message, true);
    saveMessage($pdo, $conversationId, 'bot',     $safeReply, true);

    echo json_encode([
        'conversation_id' => $conversationId,
        'reply'           => $safeReply,
        'crisis'          => true,
        'recommendations' => [],
    ]);
    exit;
}

saveMessage($pdo, $conversationId, 'student', $message, false);

$stmt = $pdo->prepare("SELECT * FROM checkins WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$studentId]);
$latestCheckin = $stmt->fetch();

// ── BUG 3 FIX: was storing evaluated conditions in $allConditions (a local-only
// variable) while $conditions stayed [] forever.  buildSystemPrompt() and
// logConditions() both received an empty array — nothing reached the LLM prompt
// and nothing was ever written to condition_logs.  Now we assign directly to $conditions.
$conditions      = [];
$recommendations = [];
$engine          = new RecommendationEngine($pdo);

if ($latestCheckin) {
    $evaluator  = new ConditionEvaluator();
    $conditions = $evaluator->evaluate($latestCheckin);   // ← fixed assignment

    $severityRank = ['severe' => 3, 'moderate' => 2, 'mild' => 1];

    if ($isFirstMessage) {
        // First reply: surface recommendations for the single worst condition only.
        $topCondition = null;
        $topRank      = 0;
        foreach ($conditions as $type => $severity) {
            if (($severityRank[$severity] ?? 0) > $topRank) {
                $topCondition = [$type => $severity];
                $topRank      = $severityRank[$severity];
            }
        }
        if ($topCondition) {
            $recommendations = $engine->getRecommendations($topCondition, 2);
        }
    } else {
        // Follow-up: let the LLM classify the concern in the message.
        $detected = detectConditionFromMessage($message, GROQ_API_KEY, GROQ_MODEL);
        if ($detected && isset($conditions[$detected])) {
            $recommendations = $engine->getRecommendations([$detected => $conditions[$detected]], 2);
        } elseif ($detected) {
            $recommendations = $engine->getRecommendations([$detected => 'moderate'], 2);
        }
        // No match → no chips; conversation flows naturally.
    }
}

$stmt = $pdo->prepare(
    "SELECT sender, message_text FROM messages
     WHERE conversation_id = ? ORDER BY created_at DESC LIMIT 6"
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

$recIds           = [];
$flatRecommendations = [];
foreach ($recommendations as $list) {
    foreach ($list as $rec) {
        $recIds[]              = $rec['rec_id'];
        $flatRecommendations[] = $rec;
    }
}
saveMessage($pdo, $conversationId, 'bot', $reply, false, implode(',', $recIds));

if (!empty($conditions)) {
    $engine->logConditions($studentId, $conditions, 'checkin', $latestCheckin['checkin_id'] ?? null);
}

echo json_encode([
    'conversation_id' => $conversationId,
    'reply'           => $reply,
    'crisis'          => false,
    'recommendations' => $flatRecommendations,
]);

// ── Helper functions ──────────────────────────────────────────────────────────

function saveMessage(
    PDO    $pdo,
    int    $conversationId,
    string $sender,
    string $text,
    bool   $flagged,
    string $recIds = ''
): void {
    $stmt = $pdo->prepare(
        "INSERT INTO messages (conversation_id, sender, message_text, flagged_crisis, recommended_rec_ids)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$conversationId, $sender, $text, $flagged ? 1 : 0, $recIds ?: null]);
}

function detectConditionFromMessage(string $message, string $apiKey, string $model): ?string
{
    $validConditions = ['academic', 'health', 'schedule', 'social', 'financial', 'sleep'];

    $payload = json_encode([
        'model'       => $model,
        'temperature' => 0,
        'max_tokens'  => 10,
        'messages'    => [
            [
                'role'    => 'system',
                'content' => "You are a classifier. Given a student's message, respond with EXACTLY one word "
                    . "from this list that best describes the student's concern: "
                    . "academic, health, schedule, social, financial, sleep. "
                    . "If the message has no clear concern related to student wellbeing, respond with: none. "
                    . "No explanation. No punctuation. One word only.",
            ],
            ['role' => 'user', 'content' => $message],
        ],
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT => 8,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ── BUG 1 FIX: previously only checked (!$response), ignoring non-200 codes.
    // On any API error the response body is a JSON error object that has no
    // "choices" key.  Accessing $data['choices'][0]['message']['content']
    // on that object causes four PHP 8.x Warnings about null/undefined offsets.
    // With XAMPP's display_errors = On those warnings are written into the
    // HTTP response *before* the JSON payload, making res.json() throw on the
    // client and triggering the "Something went wrong" catch block.
    // Fix: bail out immediately on any non-200, and use isset() before drilling
    // into the response structure so no offset warnings can ever be emitted.
    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);

    // Safe nested access — no PHP warnings even if the shape is unexpected.
    if (!isset($data['choices'][0]['message']['content'])) {
        return null;
    }

    $result = strtolower(trim($data['choices'][0]['message']['content']));
    return in_array($result, $validConditions, true) ? $result : null;
}

function buildSystemPrompt(array $conditions, array $recommendations): string
{
    $base = "You are a Guidance Counselor AI Chatbot for PUP (Polytechnic University of the Philippines) students. "
        . "Your sole purpose is to support students with concerns related to their academic life, mental and emotional "
        . "wellbeing, peer and group relationships, workload and schedule management, financial stress, and campus "
        . "resources. You listen with empathy, ask clarifying questions, validate feelings, and offer practical "
        . "next steps the way a real campus counselor would. "
        . "You are NOT a general-purpose assistant. If a student asks something outside your scope — such as "
        . "homework answers, trivia, translation requests, technical help, or anything unrelated to student "
        . "wellbeing and academic life — respond warmly but redirect them. Example: 'That's a bit outside what "
        . "I can help with, but I'm here if anything about your studies, workload, or wellbeing is on your mind.' "
        . "You are not a licensed therapist and cannot diagnose mental health conditions. "
        . "Keep replies to 3-5 sentences, warm and non-judgmental. "
        . "If recommendations are listed below, introduce them warmly as something you noticed from "
        . "the student's check-in — not as a prescription. Weave at most 1 into your opening reply naturally. "
        . "For follow-up messages with no recommendations listed, do NOT invent suggestions unprompted — "
        . "focus on understanding the student's situation first. "
        . "Always respond in the same language or mix the student uses — if they write in Taglish, respond in Taglish. "
        . "End personal topic replies with one open follow-up question.";

    if (!empty($conditions)) {
        $base .= "\n\nDetected conditions: " . json_encode($conditions);
    }
    if (!empty($recommendations)) {
        $flat = [];
        foreach ($recommendations as $list) {
            foreach ($list as $rec) {
                $flat[] = $rec['activity_text'];
            }
        }
        $base .= "\nSuggested resources to consider mentioning: " . json_encode($flat);
    }

    return $base;
}
