<?php

class GroqClient
{
    private string $apiKey;
    private string $model;
    private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(string $apiKey, string $model)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function chat(array $messages): string
    {
        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.6,
            'max_tokens'  => 500,
        ]);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Groq request failed: ' . $error);
        }
        if ($httpCode !== 200) {
            error_log('Groq API error (' . $httpCode . '): ' . $response);
            throw new RuntimeException('Groq API returned status ' . $httpCode);
        }

        $data = json_decode($response, true);
        $reply = $data['choices'][0]['message']['content'] ?? null;

        if ($reply === null) {
            throw new RuntimeException('Unexpected Groq response format');
        }

        return trim($reply);
    }
}