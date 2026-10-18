<?php

declare(strict_types=1);

namespace App\services;

/**
 * Class AIService
 *
 * Handles communication with DeepSeek and OpenAI APIs for AI-powered
 * blog storytelling, article generation, and auto-summarization.
 */
class AIService
{
    /**
     * Generate a complete, polished family blog post draft from a topic or idea.
     *
     * @param string $topic
     * @param string $tone
     * @return array{title: string, summary: string, content: string}
     */
    public static function generateBlogDraft(string $topic, string $tone = 'warm, inspiring, and family-focused'): array
    {
        $cleanTopic = self::promptText($topic, 300);
        $cleanTone = self::promptText($tone, 50);
        $systemPrompt = "You are an award-winning editorial writer and family life historian for FamilyPlatform, a private family social network and interactive family tree archive. Write with a $cleanTone tone. Never output conversational pleasantries, preambles, or markdown wrappers like ```json. Return ONLY raw JSON.";

        $userPrompt = "Write an engaging, heartwarming, and practical family blog post about: \"$cleanTopic\".
Return a JSON object with exactly these three keys:
- \"title\": A compelling, emotional, and catchy headline (50-85 characters).
- \"summary\": A punchy, SEO-optimized summary under 80 words suitable for social feeds and cards.
- \"content\": The full article written in clean HTML using only <p>, <h2>, <h3>, <blockquote>, <ul>, and <li> tags. Make it informative, practical, and emotionally resonant with 3-5 rich sections.";

        $response = self::askAI($systemPrompt, $userPrompt, 2500);

        // Strip markdown backticks if returned
        $cleaned = trim($response);
        if (preg_match('/```json\s*(.*?)\s*```/s', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $cleaned, $matches)) {
            $cleaned = trim($matches[1]);
        }

        $decoded = json_decode($cleaned, true);

        if (is_array($decoded) && !empty($decoded['title']) && !empty($decoded['content'])) {
            return [
                'title' => trim((string) $decoded['title']),
                'summary' => trim((string) ($decoded['summary'] ?? '')),
                'content' => self::purifyHtml((string) $decoded['content']),
            ];
        }

        // Fallback if JSON format was slightly imperfect
        return [
            'title' => ucwords(trim($cleanTopic)),
            'summary' => 'A thoughtful exploration on preserving family memories and strengthening generational bonds.',
            'content' => self::purifyHtml($response),
        ];
    }

    /**
     * Generates a concise summary (≤ 90 words) for an existing blog post.
     *
     * @param string $content
     * @return string
     */
    public static function summarizeBlog(string $content): string
    {
        $cleanContent = self::promptText(strip_tags($content), 4000);
        $systemPrompt = "You are an expert editorial editor for FamilyPlatform. Summarize articles into concise, heartwarming, and actionable excerpts.";
        $userPrompt = "Summarize the following family article into an engaging preview paragraph of no more than 80 words for social media and card previews:\n\n$cleanContent";

        $response = self::askAI($systemPrompt, $userPrompt, 160);

        return trim(strip_tags($response));
    }

    /**
     * Sanitizes rich HTML content, completely neutralizing XSS vectors, inline event handlers,
     * dangerous protocols, and unwhitelisted tags/attributes.
     *
     * @param string $html
     * @return string
     */
    public static function purifyHtml(string $html): string
    {
        $trimmed = trim($html);
        if ($trimmed === '') {
            return '';
        }

        // Fast-path: if no tags present, escape and return
        if (!str_contains($trimmed, '<')) {
            return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
        }

        $doc = new \DOMDocument();
        $prevErrors = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $trimmed . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $allowedTags = [
            'p', 'br', 'b', 'strong', 'i', 'em', 'u', 'h2', 'h3', 'h4',
            'ul', 'ol', 'li', 'blockquote', 'a', 'div', 'span'
        ];

        $allowedAttributes = [
            'a' => ['href', 'title', 'target', 'rel'],
        ];

        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//*');

        if ($nodes !== false) {
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $node = $nodes->item($i);
                if (!$node instanceof \DOMElement) {
                    continue;
                }

                $tagName = strtolower($node->nodeName);

                // Skip outer wrapper div
                if ($tagName === 'div' && $node->parentNode === $doc) {
                    continue;
                }

                if (!in_array($tagName, $allowedTags, true)) {
                    if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input', 'button'], true)) {
                        $node->parentNode?->removeChild($node);
                    } else {
                        while ($node->hasChildNodes() && $node->firstChild !== null) {
                            $node->parentNode?->insertBefore($node->firstChild, $node);
                        }
                        $node->parentNode?->removeChild($node);
                    }
                    continue;
                }

                // Sanitize attributes
                if ($node->hasAttributes()) {
                    $allowedForTag = $allowedAttributes[$tagName] ?? [];
                    for ($a = $node->attributes->length - 1; $a >= 0; $a--) {
                        $attr = $node->attributes->item($a);
                        if (!$attr instanceof \DOMAttr) {
                            continue;
                        }

                        $attrName = strtolower($attr->name);

                        // Neutralize on* handlers (onfocus, onmouseover, onclick, etc.) and non-whitelisted attributes
                        if (!in_array($attrName, $allowedForTag, true) || str_starts_with($attrName, 'on')) {
                            $node->removeAttribute($attr->name);
                            continue;
                        }

                        // Validate href protocols
                        if ($attrName === 'href') {
                            $val = trim($attr->value);
                            if (preg_match('/^(javascript|data|vbscript):/i', $val)) {
                                $node->removeAttribute($attr->name);
                            }
                        }
                    }

                    if ($tagName === 'a' && $node->hasAttribute('href')) {
                        $node->setAttribute('rel', 'noopener noreferrer');
                    }
                }
            }
        }

        $container = $doc->getElementsByTagName('div')->item(0);
        $output = '';
        if ($container !== null) {
            foreach ($container->childNodes as $child) {
                $output .= $doc->saveHTML($child);
            }
        }

        return trim($output);
    }

    /**
     * Truncates and sanitizes input prompt text.
     *
     * @param string $value
     * @param int $maxLength
     * @return string
     */
    private static function promptText(string $value, int $maxLength): string
    {
        $truncated = function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
        return trim(htmlspecialchars($truncated, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Orchestrates the call to DeepSeek or OpenAI based on available API keys.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param int $maxTokens
     * @return string
     */
    private static function askAI(string $systemPrompt, string $userPrompt, int $maxTokens): string
    {
        $deepseekKey = (string) ($_ENV['DEEPSEEK_API_KEY'] ?? getenv('DEEPSEEK_API_KEY') ?: '');
        $openAIKey = (string) ($_ENV['CHATGPT_API'] ?? getenv('CHATGPT_API') ?: '');

        if (!empty($deepseekKey)) {
            $result = self::callDeepSeek($systemPrompt, $userPrompt, $deepseekKey, $maxTokens);
            if (!empty($result) && !str_starts_with($result, 'AI service error')) {
                return $result;
            }
        }

        if (!empty($openAIKey)) {
            $result = self::callOpenAI($systemPrompt, $userPrompt, $openAIKey, $maxTokens);
            if (!empty($result) && !str_starts_with($result, 'AI service error')) {
                return $result;
            }
        }

        return 'AI story generation is currently unavailable. Please verify your API keys or enter content manually.';
    }

    /**
     * Call DeepSeek Chat API.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param string $apiKey
     * @param int $maxTokens
     * @return string
     */
    private static function callDeepSeek(string $systemPrompt, string $userPrompt, string $apiKey, int $maxTokens): string
    {
        $url = 'https://api.deepseek.com/chat/completions';
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ];

        return self::curlRequest($url, $apiKey, $data, 'deepseek');
    }

    /**
     * Call OpenAI Chat API.
     *
     * @param string $systemPrompt
     * @param string $userPrompt
     * @param string $apiKey
     * @param int $maxTokens
     * @return string
     */
    private static function callOpenAI(string $systemPrompt, string $userPrompt, string $apiKey, int $maxTokens): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
        ];

        return self::curlRequest($url, $apiKey, $data, 'openai');
    }

    /**
     * Execute cURL request to LLM provider.
     *
     * @param string $url
     * @param string $apiKey
     * @param array<string, mixed> $data
     * @param string $provider
     * @return string
     */
    private static function curlRequest(string $url, string $apiKey, array $data, string $provider): string
    {
        $payload = json_encode($data);
        if ($payload === false) {
            error_log("Error: AIService ($provider) failed to json_encode payload.");
            return 'AI service error: invalid payload encoding.';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $apiKey",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr !== '') {
            error_log("Error: AIService ($provider) cURL error: $curlErr");
            return "AI service error: connection timeout or network issue.";
        }

        if ($httpCode === 200 && is_string($response)) {
            $result = json_decode($response, true);
            if (is_array($result) && !empty($result['choices'][0]['message']['content'])) {
                return (string) $result['choices'][0]['message']['content'];
            }
        }

        error_log("Error: AIService ($provider) returned HTTP $httpCode. Response: " . substr((string)$response, 0, 300));
        return "AI service error: provider returned status $httpCode.";
    }
}
