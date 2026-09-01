<?php
declare(strict_types=1);

namespace App\services;

use Exception;

class AIService
{
    private const DEEPSEEK_API_URL = 'https://api.deepseek.com/chat/completions';

    /**
     * Generates a family biography securely using the DeepSeek API.
     * Enforces GDPR structural mandates (timeout handling, PII sanitization).
     *
     * @param string $firstName
     * @param string $lastName
     * @param list<string|mixed> $publicPosts
     * @return string
     * @throws Exception
     */
    public static function generateBiography(string $firstName, string $lastName, array $publicPosts): string
    {
        $apiKey = getenv('DEEPSEEK_API_KEY');
        
        if (empty($apiKey)) {
            return "AI Summary is currently unavailable (API key not configured).";
        }

        // GDPR PII Sanitization: Strip emails, numbers, or sensitive patterns from public posts before sending
        $sanitizedPosts = array_map(function($post) {
            $post = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL REMOVED]', (string)$post);
            $post = preg_replace('/\+?[0-9\s\-()]{10,20}/', '[NUMBER REMOVED]', (string)$post);
            return $post;
        }, $publicPosts);

        // Prevent Prompt Injection
        $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
        $lastName = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
        $safePosts = htmlspecialchars(implode("\n", $sanitizedPosts), ENT_QUOTES, 'UTF-8');
        $prompt = "Write a warm, family-oriented, 2-3 paragraph biography for {$firstName} {$lastName} based on the following public family posts: \n\n" . $safePosts . "\n\nDo not include any sensitive information. If there are no posts, simply write a welcoming intro for them into the family network.";

        $data = [
            "model" => "deepseek-chat",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are an expert biographer writing warm, family-oriented biographies."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ]
        ];

        $ch = curl_init(self::DEEPSEEK_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data) ?: '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        // David's Structural Gate: Timeout enforcement (prevent blocking PHP thread)
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("AIService cURL error: " . $error);
            return "AI generation timed out. Please try again later.";
        }
        
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("AIService HTTP Error: " . $httpCode . " Response: " . $response);
            return "AI service is currently unavailable.";
        }

        $responseData = json_decode((string)$response, true);
        
        // Defensive parsing (Never Trust the Data)
        if (isset($responseData['choices'][0]['message']['content'])) {
            // The biography is prose, not markup — strip any tags the model emits.
            return trim(strip_tags((string) $responseData['choices'][0]['message']['content']));
        }

        return "Unable to generate biography at this time.";
    }
}
