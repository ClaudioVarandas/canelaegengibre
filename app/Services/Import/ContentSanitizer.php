<?php

declare(strict_types=1);

namespace App\Services\Import;

class ContentSanitizer
{
    public function __construct(
        private readonly string $legacyBaseUrl = 'http://canelaegengibre.pt',
    ) {}

    /**
     * Decode HTML entities and strip legacy absolute URLs so they become site-relative.
     */
    public function sanitize(string $html): string
    {
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = str_replace("\r\n", "\n", $decoded);
        $decoded = str_replace("\r", "\n", $decoded);

        $escapedBase = preg_quote($this->legacyBaseUrl, '/');
        $decoded = preg_replace(
            '/https?:\/\/(www\.)?canelaegengibre\.pt/i',
            '',
            $decoded,
        );

        return trim((string) $decoded);
    }

    /**
     * Extract upload paths referenced by the given HTML content.
     *
     * @return list<string>
     */
    public function extractUploadPaths(string $html): array
    {
        if (! preg_match_all('/(?:src|href)=(["\'])((?:https?:\/\/[^"\']+)?\/uploads\/[^"\']+)\1/i', $html, $matches)) {
            return [];
        }

        $paths = array_map(function (string $url): string {
            $path = parse_url($url, PHP_URL_PATH) ?: $url;

            return ltrim((string) $path, '/');
        }, $matches[2]);

        return array_values(array_unique($paths));
    }
}
