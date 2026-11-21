<?php

namespace App\Services;

class DocumentSummarizer
{
    protected static array $stopWords = [
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'aren\'t', 'as', 'at',
        'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can', 'cannot', 'could',
        'did', 'do', 'does', 'doing', 'down', 'during', 'each', 'few', 'for', 'from', 'further', 'had', 'has', 'have',
        'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'i', 'if', 'in', 'into', 'is',
        'isn\'t', 'it', 'its', 'itself', 'just', 'me', 'more', 'most', 'my', 'myself', 'no', 'nor', 'not', 'now', 'of',
        'off', 'on', 'once', 'only', 'or', 'other', 'ought', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 'same',
        'she', 'should', 'so', 'some', 'such', 'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then',
        'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was', 'wasn\'t',
        'we', 'were', 'weren\'t', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would', 'you',
        'your', 'yours', 'yourself', 'yourselves'
    ];

    /**
     * Generate an intelligent summary of the given text.
     *
     * @param string $text
     * @param int $maxSentences
     * @return array
     */
    public function summarize(string $text, int $maxSentences = 5): array
    {
        $cleanText = trim(preg_replace('/\s+/', ' ', $text));
        if (empty($cleanText)) {
            return [
                'summary' => 'No readable text could be found to summarize.',
                'key_points' => [],
                'stats' => [
                    'original_words' => 0,
                    'summary_words' => 0,
                    'reduction' => 0,
                    'orig_reading_time' => '< 1 min',
                    'summary_reading_time' => '< 1 min',
                ],
            ];
        }

        $wordCount = str_word_count($cleanText);

        // Split text into candidate sentences
        $rawSentences = preg_split('/(?<=[.?!])\s+(?=[A-Z0-9])/u', $text);
        if (count($rawSentences) <= 1) {
            $rawSentences = preg_split('/[\r\n]+/', $text);
        }

        $seen = [];
        $sentences = [];
        foreach ($rawSentences as $sentence) {
            $s = trim(preg_replace('/\s+/', ' ', $sentence));
            $sWords = str_word_count($s);
            $norm = strtolower(preg_replace('/[^a-z0-9]/', '', $s));

            // Filter out short fragments, repetitive slide headers, and duplicate sentences
            if ($sWords >= 7 && $sWords <= 80 && strlen($norm) > 15 && !isset($seen[$norm])) {
                $seen[$norm] = true;
                $sentences[] = ucfirst(trim($s, " \t\n\r\0\x0B."));
            }
        }

        if (count($sentences) === 0) {
            $sentences = array_filter(array_map('trim', explode("\n", $text)));
        }

        // If the document is already very short
        if (count($sentences) <= $maxSentences) {
            $summaryText = implode('. ', $sentences) . '.';
            return [
                'summary' => $summaryText,
                'key_points' => array_values(array_map(fn($s) => $s . '.', array_slice($sentences, 0, 4))),
                'stats' => $this->calculateStats($wordCount, str_word_count($summaryText)),
            ];
        }

        // 1. Calculate word frequency (excluding stop words)
        $words = preg_split('/[^a-zA-Z0-9]+/', strtolower($cleanText), -1, PREG_SPLIT_NO_EMPTY);
        $freq = [];
        $stopWordMap = array_flip(self::$stopWords);

        foreach ($words as $word) {
            if (strlen($word) >= 3 && !isset($stopWordMap[$word]) && !is_numeric($word)) {
                $freq[$word] = ($freq[$word] ?? 0) + 1;
            }
        }

        // 2. Score sentences based on term density and structural position
        $scored = [];
        $totalSentences = count($sentences);

        foreach ($sentences as $index => $sentence) {
            $sentenceWords = preg_split('/[^a-zA-Z0-9]+/', strtolower($sentence), -1, PREG_SPLIT_NO_EMPTY);
            $sentenceWordCount = count($sentenceWords);

            if ($sentenceWordCount === 0) continue;

            $score = 0;
            foreach ($sentenceWords as $w) {
                if (isset($freq[$w])) {
                    $score += $freq[$w];
                }
            }

            // Normalize score by sentence length to balance short and long sentences
            $normalizedScore = $score / sqrt($sentenceWordCount);

            // Structural weightings:
            if ($index === 0) {
                $normalizedScore *= 1.4;
            } elseif ($index < 4) {
                $normalizedScore *= 1.25;
            } elseif ($index > ($totalSentences - 4)) {
                $normalizedScore *= 1.2;
            }

            // Keyword boost for significant informative terms
            if (preg_match('/\b(conclude|conclusion|summary|results|strategy|growth|objective|target|prescribed|significant|important|shows|demonstrates|key|vital|analysis)\b/i', $sentence)) {
                $normalizedScore *= 1.35;
            }

            $scored[] = [
                'index' => $index,
                'sentence' => $sentence,
                'words' => array_flip($sentenceWords),
                'score' => $normalizedScore,
            ];
        }

        // Sort candidate sentences by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        // Pick top N diverse sentences (avoiding sentence overlap)
        $selected = [];
        foreach ($scored as $item) {
            $tooSimilar = false;
            foreach ($selected as $sel) {
                $intersection = count(array_intersect_key($item['words'], $sel['words']));
                $union = count($item['words'] + $sel['words']);
                if ($union > 0 && ($intersection / $union) > 0.45) {
                    $tooSimilar = true;
                    break;
                }
            }

            if (!$tooSimilar) {
                $selected[] = $item;
            }

            if (count($selected) >= $maxSentences) {
                break;
            }
        }

        // Re-sort chosen sentences by chronological order for natural reading flow
        usort($selected, fn($a, $b) => $a['index'] <=> $b['index']);

        $summarySentences = array_map(fn($item) => $item['sentence'] . '.', $selected);
        $summaryText = implode(' ', $summarySentences);

        // Key Bullet Points: format each distinct point
        $bulletPoints = [];
        foreach ($selected as $item) {
            $bulletPoints[] = $item['sentence'] . '.';
            if (count($bulletPoints) >= 4) break;
        }

        return [
            'summary' => $summaryText,
            'key_points' => $bulletPoints,
            'stats' => $this->calculateStats($wordCount, str_word_count($summaryText)),
        ];
    }

    protected function calculateStats(int $origWords, int $summaryWords): array
    {
        $origWords = max(1, $origWords);
        $summaryWords = max(1, $summaryWords);
        $reduction = max(0, round((($origWords - $summaryWords) / $origWords) * 100));

        $origMin = ceil($origWords / 200);
        $summaryMin = ceil($summaryWords / 200);

        return [
            'original_words' => $origWords,
            'summary_words' => $summaryWords,
            'reduction' => $reduction,
            'orig_reading_time' => $origMin . ' min' . ($origMin > 1 ? 's' : ''),
            'summary_reading_time' => $summaryMin . ' min' . ($summaryMin > 1 ? 's' : ''),
        ];
    }
}
