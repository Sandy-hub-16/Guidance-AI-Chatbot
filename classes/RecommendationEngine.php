<?php

class RecommendationEngine
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db; // pass in $pdo from db.php — this class never connects itself
    }

    public function getRecommendations(array $conditions, int $maxPerCondition = 2): array
    {
        $results = [];

        $stmt = $this->db->prepare(
            "SELECT rec_id, activity_text, activity_type
             FROM recommendations
             WHERE condition_type = :type
               AND severity = :severity
               AND is_active = 1
             LIMIT :limit"
        );

        foreach ($conditions as $type => $severity) {
            $stmt->bindValue(':type', $type, PDO::PARAM_STR);
            $stmt->bindValue(':severity', $severity, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $maxPerCondition, PDO::PARAM_INT);
            $stmt->execute();

            $results[$type] = $stmt->fetchAll();
        }

        return $results;
    }

    public function logConditions(int $studentId, array $conditions, string $source, ?int $sourceId = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO condition_logs (student_id, condition_type, severity, source, source_id)
             VALUES (:student_id, :type, :severity, :source, :source_id)"
        );

        foreach ($conditions as $type => $severity) {
            $stmt->execute([
                ':student_id' => $studentId,
                ':type'       => $type,
                ':severity'   => $severity,
                ':source'     => $source,
                ':source_id'  => $sourceId,
            ]);
        }
    }
}