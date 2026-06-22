<?php

class CrisisFilter
{
    private PDO $db;
    private ?array $keywords = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function isCrisis(string $message): bool
    {
        if ($this->keywords === null) {
            $stmt = $this->db->query("SELECT keyword FROM crisis_keywords");
            $this->keywords = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $normalized = mb_strtolower($message);

        foreach ($this->keywords as $keyword) {
            if (mb_strpos($normalized, mb_strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getSafeResponse(): string
    {
        return "I want to make sure you get real support right now, not just a reply from me. "
             . "Please reach out to the PUP Guidance Office or someone you trust nearby. You can "
             . "also call the NCMH Crisis Hotline, free and available 24/7: 1553 (landline, toll-free) "
             . "or 0917-899-8727 / 0919-057-1553 (mobile). You don't have to go through this alone.";
    }
}