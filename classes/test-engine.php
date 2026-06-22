<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/ConditionEvaluator.php';
require_once __DIR__ . '/RecommendationEngine.php';

$fakeCheckin = [
    'sleep_hours'             => 4.5,
    'mood_score'               => 2,
    'attendance_pct'            => 80,
    'gpa_self_report'           => 2.2,
    'workload_score'            => 4,
    'social_score'                => 2,
    'financial_stress_score'   => 1,
];

$evaluator = new ConditionEvaluator();
$conditions = $evaluator->evaluate($fakeCheckin);

$engine = new RecommendationEngine($pdo);
$recs = $engine->getRecommendations($conditions);

echo "<pre>";
print_r($conditions);
print_r($recs);
echo "</pre>";