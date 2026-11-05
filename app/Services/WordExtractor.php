<?php

namespace App\Services;

use App\Repositories\Contracts\WordRepositoryInterface;

class WordExtractor
{
    public function __construct(
        private readonly WordRepositoryInterface $words,
    ) {}

    /**
     * Split the given text into words and return the id of each word, in the order it
     * appears in the text. Words that don't exist yet for the given language are created.
     *
     * @return array<int, int>
     */
    public function extract(string $text, int $languageId): array
    {
        $words = $this->split($text);

        if ($words === []) {
            return [];
        }

        return $this->words->resolveIds($languageId, $words);
    }

    /**
     * @return array<int, string>
     */
    private function split(string $text): array
    {
        preg_match_all("/[\p{L}'-]+/u", $text, $matches);

        return array_map(fn (string $word) => mb_strtolower($word), $matches[0]);
    }
}
