<?php

declare(strict_types=1);

namespace App\services;

/**
 * 🤖 OmniSearchMlEngine: ATG Sovereign Applied ML Intent & Semantic Search Engine
 *
 * Zero-dependency PHP 8.2+ NLP & Vector Space Model for OmniSearch:
 * - NLP preprocessing: punctuation stripping, UK/US normalisation, stopword removal,
 *   lightweight Porter-style stemming, and domain concept expansion.
 * - TF-IDF Vector Space Model with Cosine Similarity + Bigram & Character Trigram
 *   (fuzzy) matching over the action catalog, running in <= O(n log n) time.
 * - Calibrated Intent Confidence & Deterministic Rule-Based Fallback (< 95% threshold)
 *   per ATG Deep-Tech Standards.
 */
final class OmniSearchMlEngine
{
    private const CONFIDENCE_THRESHOLD = 0.95;

    /** @var array<string, true> */
    private const STOPWORDS = [
        'i' => true, 'me' => true, 'my' => true, 'myself' => true, 'we' => true,
        'our' => true, 'you' => true, 'your' => true, 'he' => true, 'she' => true,
        'it' => true, 'its' => true, 'they' => true, 'them' => true, 'what' => true,
        'which' => true, 'who' => true, 'whom' => true, 'this' => true, 'that' => true,
        'these' => true, 'those' => true, 'am' => true, 'is' => true, 'are' => true,
        'was' => true, 'were' => true, 'be' => true, 'been' => true, 'being' => true,
        'have' => true, 'has' => true, 'had' => true, 'do' => true, 'does' => true,
        'did' => true, 'a' => true, 'an' => true, 'the' => true, 'and' => true,
        'but' => true, 'if' => true, 'or' => true, 'because' => true, 'as' => true,
        'until' => true, 'while' => true, 'of' => true, 'at' => true, 'by' => true,
        'for' => true, 'with' => true, 'about' => true, 'against' => true,
        'between' => true, 'into' => true, 'through' => true, 'during' => true,
        'before' => true, 'after' => true, 'above' => true, 'below' => true,
        'to' => true, 'from' => true, 'up' => true, 'down' => true, 'in' => true,
        'out' => true, 'on' => true, 'off' => true, 'over' => true, 'under' => true,
        'again' => true, 'further' => true, 'then' => true, 'once' => true,
        'here' => true, 'there' => true, 'when' => true, 'where' => true, 'why' => true,
        'how' => true, 'all' => true, 'any' => true, 'both' => true, 'each' => true,
        'few' => true, 'more' => true, 'most' => true, 'other' => true, 'some' => true,
        'such' => true, 'no' => true, 'nor' => true, 'not' => true, 'only' => true,
        'own' => true, 'same' => true, 'so' => true, 'than' => true, 'too' => true,
        'very' => true, 'can' => true, 'will' => true, 'just' => true, 'should' => true,
        'now' => true, 'want' => true, 'need' => true, 'would' => true, 'like' => true,
        'could' => true, 'please' => true, 'get' => true, 'go' => true,
    ];

    /**
     * Domain semantic concept map: normalises natural language variants,
     * UK/US spellings, and occasion/intent synonyms into canonical concept stems.
     *
     * @var array<string, list<string>>
     */
    private const CONCEPT_EXPANSIONS = [
        'celebratory' => ['celebrat', 'festiv', 'card', 'greet'],
        'celebration' => ['celebrat', 'festiv', 'card', 'greet'],
        'celebrate' => ['celebrat', 'festiv', 'card', 'greet'],
        'festive' => ['festiv', 'celebrat', 'card', 'greet'],
        'festivity' => ['festiv', 'celebrat', 'card', 'greet'],
        'greeting' => ['greet', 'card', 'celebrat', 'festiv'],
        'greetings' => ['greet', 'card', 'celebrat', 'festiv'],
        'ecard' => ['card', 'greet', 'celebrat', 'festiv'],
        'ecards' => ['card', 'greet', 'celebrat', 'festiv'],
        'card' => ['card', 'greet', 'celebrat', 'festiv'],
        'cards' => ['card', 'greet', 'celebrat', 'festiv'],
        'birthday' => ['birthday', 'celebrat', 'card', 'festiv'],
        'anniversary' => ['anniversari', 'celebrat', 'card', 'festiv'],
        'congratulations' => ['congratul', 'celebrat', 'card', 'festiv'],
        'congrats' => ['congratul', 'celebrat', 'card', 'festiv'],
        'wedding' => ['wed', 'celebrat', 'card', 'festiv'],
        'christmas' => ['christma', 'festiv', 'celebrat', 'card'],
        'easter' => ['easter', 'festiv', 'celebrat', 'card'],
        'eid' => ['eid', 'festiv', 'celebrat', 'card'],
        'diwali' => ['diwali', 'festiv', 'celebrat', 'card'],
        'envelope' => ['envelop', 'card', 'festiv', 'celebrat'],
        'poster' => ['poster', 'flyer', 'design', 'studio'],
        'posters' => ['poster', 'flyer', 'design', 'studio'],
        'flyer' => ['flyer', 'poster', 'design', 'studio'],
        'flyers' => ['flyer', 'poster', 'design', 'studio'],
        'invitation' => ['invit', 'poster', 'flyer', 'card'],
        'gif' => ['gif', 'animat', 'sticker', 'studio'],
        'gifs' => ['gif', 'animat', 'sticker', 'studio'],
        'emoji' => ['emoji', 'avatar', 'sticker', 'studio'],
        'emojis' => ['emoji', 'avatar', 'sticker', 'studio'],
        'avatar' => ['avatar', 'emoji', 'studio'],
        'studio' => ['studio', 'design', 'creat'],
        'create' => ['creat', 'make', 'design'],
        'make' => ['creat', 'make', 'design'],
        'design' => ['design', 'creat', 'studio'],
        'build' => ['creat', 'make'],
        'craft' => ['creat', 'design'],
        'send' => ['send', 'share'],
        'mum' => ['mother', 'parent', 'relat'],
        'mom' => ['mother', 'parent', 'relat'],
        'dad' => ['father', 'parent', 'relat'],
        'grandma' => ['grandmoth', 'relat', 'tree'],
        'grandpa' => ['grandfath', 'relat', 'tree'],
        'organize' => ['organis'],
        'customize' => ['customis'],
        'color' => ['colour'],
        'neighbor' => ['neighbour'],
        'center' => ['centr'],
        'password' => ['password', 'secur', 'pin', 'passkey'],
        'passkey' => ['passkey', 'secur', 'biometr'],
        'logout' => ['signout', 'exit', 'logoff'],
        'signout' => ['signout', 'exit', 'logoff'],
        'event' => ['event', 'calendar', 'schedul', 'gather'],
        'events' => ['event', 'calendar', 'schedul', 'gather'],
        'calendar' => ['calendar', 'event', 'schedul'],
        'upcoming' => ['upcom', 'event', 'calendar'],
        'announcement' => ['announc', 'broadcast', 'news'],
        'announcements' => ['announc', 'broadcast', 'news'],
        'announce' => ['announc', 'broadcast', 'news'],
        'chat' => ['whisper', 'chat', 'messag'],
        'message' => ['whisper', 'messag', 'chat'],
        'whisper' => ['whisper', 'messag', 'chat'],
    ];

    /**
     * In-memory fitted TF-IDF index cache keyed by catalog fingerprint.
     *
     * @var array<string, array{
     *     idf: array<string, float>,
     *     docVectors: array<string, array<string, float>>,
     *     docNorms: array<string, float>,
     *     docTrigrams: array<string, array<string, true>>,
     *     rawTexts: array<string, string>
     * }>
     */
    private static array $indexCache = [];

    /**
     * Scores and ranks an action catalog using sovereign TF-IDF cosine similarity,
     * character trigram fuzzy similarity, and deterministic rule-based fallback.
     *
     * @param string $query
     * @param array<int, array<string, mixed>> $catalog
     * @return array<int, array<string, mixed>>
     */
    public function scoreCatalog(string $query, array $catalog): array
    {
        $cleanQuery = $this->normaliseText($query);
        if ($cleanQuery === '') {
            return [];
        }

        $index = $this->getOrBuildIndex($catalog);
        $queryTokens = $this->tokeniseAndExpand($cleanQuery);
        $queryBigrams = $this->extractBigrams($this->tokenise($cleanQuery));
        $allQueryTerms = array_merge($queryTokens, $queryBigrams);

        if (empty($allQueryTerms)) {
            return [];
        }

        // Build query TF-IDF vector
        $queryTf = array_count_values($allQueryTerms);
        $queryVec = [];
        $queryNormSq = 0.0;
        foreach ($queryTf as $term => $count) {
            $idf = $index['idf'][$term] ?? 1.2;
            $weight = (1.0 + log((float)$count)) * $idf;
            $queryVec[$term] = $weight;
            $queryNormSq += $weight * $weight;
        }
        $queryNorm = sqrt($queryNormSq);
        if ($queryNorm <= 0.0) {
            return [];
        }

        $queryTrigrams = $this->extractCharTrigrams($cleanQuery);
        $rawQueryTokens = $this->tokenise($cleanQuery);

        $scoredItems = [];
        $maxRawScore = 0.0;

        foreach ($catalog as $item) {
            $id = (string)($item['id'] ?? '');
            if ($id === '' || !isset($index['docVectors'][$id])) {
                continue;
            }

            // 1. Cosine Similarity in TF-IDF space
            $docVec = $index['docVectors'][$id];
            $docNorm = $index['docNorms'][$id];
            $dot = 0.0;
            foreach ($queryVec as $term => $qWeight) {
                if (isset($docVec[$term])) {
                    $dot += $qWeight * $docVec[$term];
                }
            }
            $cosine = ($docNorm > 0.0) ? ($dot / ($queryNorm * $docNorm)) : 0.0;

            // 2. Fuzzy Character Trigram Jaccard Similarity (typo resilience)
            $trigramSim = $this->jaccardSimilarity($queryTrigrams, $index['docTrigrams'][$id] ?? []);

            // 3. Deterministic Rule-Based Score (exact phrase, token hits, intent rules)
            $ruleScore = $this->computeDeterministicRuleScore($cleanQuery, $rawQueryTokens, $item);

            // Combined ML semantic score (0..1000 scale)
            $mlSemanticScore = ($cosine * 650.0) + ($trigramSim * 180.0);
            $combinedScore = $mlSemanticScore + $ruleScore;

            if ($combinedScore > 12.0 || $ruleScore > 0.0) {
                $maxRawScore = max($maxRawScore, $combinedScore);
                $scoredItems[] = [
                    'item' => $item,
                    'cosine' => $cosine,
                    'trigramSim' => $trigramSim,
                    'mlSemanticScore' => $mlSemanticScore,
                    'ruleScore' => $ruleScore,
                    'combinedScore' => $combinedScore,
                ];
            }
        }

        if (empty($scoredItems)) {
            return [];
        }

        // Compute softmax-calibrated ML confidence across top candidates
        $expSum = 0.0;
        $temperature = 140.0;
        foreach ($scoredItems as &$entry) {
            $expVal = exp(($entry['combinedScore'] - $maxRawScore) / $temperature);
            $entry['expVal'] = $expVal;
            $expSum += $expVal;
        }
        unset($entry);

        $results = [];
        foreach ($scoredItems as $entry) {
            $item = $entry['item'];
            $posterior = $expSum > 0.0 ? ($entry['expVal'] / $expSum) : 0.0;
            // Calibrate confidence combining posterior margin & cosine strength
            $confidence = min(0.99, max(0.05, ($posterior * 0.65) + ($entry['cosine'] * 0.45)));

            // ATG Mandate: deterministic fallback if ML confidence < 95%
            if ($confidence >= self::CONFIDENCE_THRESHOLD) {
                $matchSource = 'ml_intent';
            } elseif ($entry['ruleScore'] > 0.0 && $entry['mlSemanticScore'] > 0.0) {
                $matchSource = 'hybrid';
            } else {
                $matchSource = 'deterministic_fallback';
            }

            $priority = (int)($item['priority'] ?? 0);
            $item['matchScore'] = (int)round($entry['combinedScore']) + $priority;
            $item['mlConfidence'] = (int)round($confidence * 100);
            $item['matchSource'] = $matchSource;
            $results[] = $item;
        }

        usort(
            $results,
            static fn(array $a, array $b): int => ((int)$b['matchScore']) <=> ((int)$a['matchScore'])
        );

        return $results;
    }

    /**
     * Fits or retrieves the cached TF-IDF & trigram index for the action catalog.
     *
     * @param array<int, array<string, mixed>> $catalog
     * @return array{
     *     idf: array<string, float>,
     *     docVectors: array<string, array<string, float>>,
     *     docNorms: array<string, float>,
     *     docTrigrams: array<string, array<string, true>>,
     *     rawTexts: array<string, string>
     * }
     */
    private function getOrBuildIndex(array $catalog): array
    {
        $ids = array_map(static fn(array $item): string => (string)($item['id'] ?? ''), $catalog);
        $cacheKey = hash('sha256', implode('|', $ids) . ':' . count($catalog));

        if (isset(self::$indexCache[$cacheKey])) {
            return self::$indexCache[$cacheKey];
        }

        $docCount = max(1, count($catalog));
        $docTermFreqs = [];
        $df = [];
        $docTrigrams = [];
        $rawTexts = [];

        foreach ($catalog as $item) {
            $id = (string)($item['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $title = $this->normaliseText((string)($item['title'] ?? ''));
            $desc = $this->normaliseText((string)($item['description'] ?? ''));
            $badge = $this->normaliseText((string)($item['badge'] ?? ''));
            $keywords = array_map(
                fn($k): string => $this->normaliseText((string)$k),
                (array)($item['keywords'] ?? [])
            );

            // Field-weighted token bags (title 3x, keywords 2x, badge 2x, description 1x)
            $weightedTerms = [];
            $titleTokens = $this->tokeniseAndExpand($title);
            foreach ($titleTokens as $t) {
                $weightedTerms[$t] = ($weightedTerms[$t] ?? 0.0) + 3.0;
            }
            foreach ($this->extractBigrams($this->tokenise($title)) as $bg) {
                $weightedTerms[$bg] = ($weightedTerms[$bg] ?? 0.0) + 3.5;
            }

            foreach ($keywords as $kw) {
                foreach ($this->tokeniseAndExpand($kw) as $t) {
                    $weightedTerms[$t] = ($weightedTerms[$t] ?? 0.0) + 2.2;
                }
                foreach ($this->extractBigrams($this->tokenise($kw)) as $bg) {
                    $weightedTerms[$bg] = ($weightedTerms[$bg] ?? 0.0) + 2.8;
                }
            }

            foreach ($this->tokeniseAndExpand($badge) as $t) {
                $weightedTerms[$t] = ($weightedTerms[$t] ?? 0.0) + 1.8;
            }

            foreach ($this->tokeniseAndExpand($desc) as $t) {
                $weightedTerms[$t] = ($weightedTerms[$t] ?? 0.0) + 1.0;
            }

            $docTermFreqs[$id] = $weightedTerms;
            foreach (array_keys($weightedTerms) as $term) {
                $df[$term] = ($df[$term] ?? 0) + 1;
            }

            $combinedRaw = trim($title . ' ' . implode(' ', $keywords));
            $rawTexts[$id] = $combinedRaw;
            $docTrigrams[$id] = $this->extractCharTrigrams($combinedRaw);
        }

        // Compute smoothed IDF: log((N + 1) / (df + 1)) + 1
        $idf = [];
        foreach ($df as $term => $freq) {
            $idf[$term] = log(($docCount + 1.0) / ($freq + 1.0)) + 1.0;
        }

        $docVectors = [];
        $docNorms = [];
        foreach ($docTermFreqs as $id => $tfMap) {
            $vec = [];
            $normSq = 0.0;
            foreach ($tfMap as $term => $tf) {
                $w = (1.0 + log($tf)) * ($idf[$term] ?? 1.0);
                $vec[$term] = $w;
                $normSq += $w * $w;
            }
            $docVectors[$id] = $vec;
            $docNorms[$id] = sqrt($normSq);
        }

        self::$indexCache[$cacheKey] = [
            'idf' => $idf,
            'docVectors' => $docVectors,
            'docNorms' => $docNorms,
            'docTrigrams' => $docTrigrams,
            'rawTexts' => $rawTexts,
        ];

        return self::$indexCache[$cacheKey];
    }

    /**
     * Deterministic rule-based scoring fallback (exact phrases, token overlap, domain rules).
     *
     * @param string $cleanQuery
     * @param list<string> $rawTokens
     * @param array<string, mixed> $item
     */
    private function computeDeterministicRuleScore(string $cleanQuery, array $rawTokens, array $item): float
    {
        $score = 0.0;
        $id = (string)($item['id'] ?? '');
        $titleLower = $this->normaliseText((string)($item['title'] ?? ''));
        $descLower = $this->normaliseText((string)($item['description'] ?? ''));
        $keywords = array_map(
            fn($k): string => $this->normaliseText((string)$k),
            (array)($item['keywords'] ?? [])
        );

        if ($titleLower !== '' && str_contains($titleLower, $cleanQuery)) {
            $score += 250.0;
        }

        foreach ($keywords as $kw) {
            if ($kw === $cleanQuery) {
                $score += 300.0;
                break;
            }
            if ($kw !== '' && str_contains($kw, $cleanQuery)) {
                $score += 150.0;
            }
        }

        foreach ($rawTokens as $tok) {
            if (strlen($tok) < 2) {
                continue;
            }
            if (str_contains($titleLower, $tok)) {
                $score += 55.0;
            }
            if (str_contains($descLower, $tok)) {
                $score += 25.0;
            }
            foreach ($keywords as $kw) {
                if (str_contains($kw, $tok)) {
                    $score += 45.0;
                }
            }
        }

        // Deterministic intent guardrails
        if (str_contains($cleanQuery, 'journal') && in_array($id, ['act_family_journal', 'act_write_journal'], true)) {
            $score += 200.0;
        }
        if (str_contains($cleanQuery, 'code') && in_array($id, ['act_create_family_code', 'act_link_birth_code', 'act_rename_family_code'], true)) {
            $score += 180.0;
        }
        if (str_contains($cleanQuery, 'tree') && in_array($id, ['act_view_tree', 'act_add_relative'], true)) {
            $score += 180.0;
        }
        if ((str_contains($cleanQuery, 'card') || str_contains($cleanQuery, 'celebrat') || str_contains($cleanQuery, 'festiv'))
            && $id === 'act_festive_cards_studio'
        ) {
            $score += 240.0;
        }
        if ((str_contains($cleanQuery, 'poster') || str_contains($cleanQuery, 'flyer')) && $id === 'act_poster_studio') {
            $score += 220.0;
        }

        return $score;
    }

    private function normaliseText(string $text): string
    {
        $lower = strtolower(trim($text));
        // Strip punctuation while preserving spaces and hyphens
        $cleaned = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $lower) ?? $lower;
        return trim((string)(preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned));
    }

    /**
     * Tokenises text and removes conversational stopwords.
     *
     * @return list<string>
     */
    private function tokenise(string $normalisedText): array
    {
        if ($normalisedText === '') {
            return [];
        }
        $parts = preg_split('/\s+/', $normalisedText) ?: [];
        $tokens = [];
        foreach ($parts as $part) {
            if (strlen($part) < 2 || isset(self::STOPWORDS[$part])) {
                continue;
            }
            $tokens[] = $part;
        }
        return $tokens;
    }

    /**
     * Tokenises, stems, and expands domain concepts.
     *
     * @return list<string>
     */
    private function tokeniseAndExpand(string $normalisedText): array
    {
        $tokens = $this->tokenise($normalisedText);
        $expanded = [];
        foreach ($tokens as $tok) {
            $expanded[] = $this->stem($tok);
            if (isset(self::CONCEPT_EXPANSIONS[$tok])) {
                foreach (self::CONCEPT_EXPANSIONS[$tok] as $concept) {
                    $expanded[] = $concept;
                }
            }
        }
        return $expanded;
    }

    /**
     * Lightweight Porter-style suffix stemmer for English search tokens.
     */
    private function stem(string $word): string
    {
        $len = strlen($word);
        if ($len <= 3) {
            return $word;
        }

        if (str_ends_with($word, 'ies') && $len > 4) {
            return substr($word, 0, -3) . 'i';
        }
        if (str_ends_with($word, 'ing') && $len > 5) {
            return substr($word, 0, -3);
        }
        if (str_ends_with($word, 'tion') && $len > 5) {
            return substr($word, 0, -4) . 't';
        }
        if (str_ends_with($word, 'ory') && $len > 5) {
            return substr($word, 0, -3);
        }
        if (str_ends_with($word, 'ed') && $len > 4) {
            return substr($word, 0, -2);
        }
        if (str_ends_with($word, 'es') && $len > 4) {
            return substr($word, 0, -2);
        }
        if (str_ends_with($word, 's') && !str_ends_with($word, 'ss')) {
            return substr($word, 0, -1);
        }

        return $word;
    }

    /**
     * @param list<string> $tokens
     * @return list<string>
     */
    private function extractBigrams(array $tokens): array
    {
        $bigrams = [];
        $count = count($tokens);
        for ($i = 0; $i < $count - 1; $i++) {
            $bigrams[] = $this->stem($tokens[$i]) . '_' . $this->stem($tokens[$i + 1]);
        }
        return $bigrams;
    }

    /**
     * @return array<string, true>
     */
    private function extractCharTrigrams(string $text): array
    {
        $compact = str_replace(' ', '_', $text);
        $len = strlen($compact);
        if ($len < 3) {
            return $compact !== '' ? [$compact => true] : [];
        }

        $trigrams = [];
        for ($i = 0; $i <= $len - 3; $i++) {
            $trigrams[substr($compact, $i, 3)] = true;
        }
        return $trigrams;
    }

    /**
     * @param array<string, true> $a
     * @param array<string, true> $b
     */
    private function jaccardSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }
        $intersection = count(array_intersect_key($a, $b));
        if ($intersection === 0) {
            return 0.0;
        }
        $union = count($a) + count($b) - $intersection;
        return $union > 0 ? ($intersection / $union) : 0.0;
    }
}
