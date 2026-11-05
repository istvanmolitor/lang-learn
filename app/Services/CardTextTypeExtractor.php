<?php

namespace App\Services;

class CardTextTypeExtractor
{
    /**
     * Known part-of-speech abbreviation codes, matching card_types.code.
     *
     * @var array<int, string>
     */
    private const KNOWN_CODES = ['n', 'v', 'adj', 'adv', 'prep', 'conj', 'pron', 'det', 'exclam'];

    /**
     * Strip a trailing part-of-speech annotation (e.g. "ability (n.)" or "after (conj., adv.)")
     * from the given text and return the cleaned text along with the matching card type code.
     *
     * Parenthesised groups that aren't made up entirely of known abbreviations (e.g.
     * "bear (animal)") are left in the text untouched, since they disambiguate the word's
     * meaning rather than tag its part of speech.
     *
     * @return array{text: string, code: string|null}
     */
    public function extract(string $text): array
    {
        $code = null;

        $cleaned = preg_replace_callback(
            '/\s*\(([^)]*)\)/',
            function (array $matches) use (&$code) {
                $tokens = array_map(
                    fn (string $token) => strtolower(rtrim(trim($token), '.')),
                    explode(',', $matches[1])
                );

                if (array_diff($tokens, self::KNOWN_CODES) !== []) {
                    return $matches[0];
                }

                $code ??= $tokens[0];

                return '';
            },
            $text
        );

        return [
            'text' => trim(preg_replace('/\s+/', ' ', $cleaned ?? $text)),
            'code' => $code,
        ];
    }
}
