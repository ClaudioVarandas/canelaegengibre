<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Anthropic\Client;
use Anthropic\Messages\Message;
use Anthropic\Messages\TextBlock;
use Illuminate\Support\Str;
use RuntimeException;

class ClaudeTranslator implements TranslatorContract
{
    public function __construct(
        private readonly Client $client,
        private readonly string $model = 'claude-opus-4-7',
        private readonly int $maxTokens = 16000,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function translate(string $sourceLocale, array $source, array $targetLocales): array
    {
        $systemPrompt = $this->systemPrompt();

        $userPayload = [
            'source_locale' => $sourceLocale,
            'target_locales' => $targetLocales,
            'fields' => $source,
        ];

        $message = $this->client->messages->create(
            maxTokens: $this->maxTokens,
            messages: [[
                'role' => 'user',
                'content' => [[
                    'type' => 'text',
                    'text' => 'Translate the JSON payload below. Return JSON only, matching the required shape.'."\n\n".json_encode($userPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                ]],
            ]],
            model: $this->model,
            system: [[
                'type' => 'text',
                'text' => $systemPrompt,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
        );

        $raw = $this->extractText($message);

        return $this->parseResponse($raw, $targetLocales, array_keys($source));
    }

    /**
     * @param  Message  $message
     */
    private function extractText($message): string
    {
        $buffer = '';
        foreach ($message->content as $block) {
            if ($block instanceof TextBlock) {
                $buffer .= $block->text;
            }
        }

        return $buffer;
    }

    /**
     * @param  list<string>  $targetLocales
     * @param  list<string>  $expectedFields
     * @return array<string, array<string, string>>
     */
    private function parseResponse(string $raw, array $targetLocales, array $expectedFields): array
    {
        $json = $this->extractJson($raw);
        if ($json === null) {
            throw new RuntimeException('Claude returned a response without parseable JSON: '.Str::limit($raw, 200));
        }

        $out = [];
        foreach ($targetLocales as $locale) {
            if (! isset($json[$locale]) || ! is_array($json[$locale])) {
                throw new RuntimeException("Claude response missing locale '{$locale}'");
            }
            $localeData = [];
            foreach ($expectedFields as $field) {
                $value = $json[$locale][$field] ?? null;
                $localeData[$field] = is_string($value) ? $value : '';
            }
            $out[$locale] = $localeData;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $candidate = $raw;
        if (preg_match('/```(?:json)?\s*(.+?)\s*```/s', $raw, $match)) {
            $candidate = $match[1];
        }

        $decoded = json_decode($candidate, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $candidate, $match)) {
            $decoded = json_decode($match[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        You are a professional translator for a Portuguese-from-Portugal food and lifestyle blog called "Canela e Gengibre".

        Input will be a JSON object with:
        - source_locale: e.g. "pt_PT"
        - target_locales: an array like ["pt_BR", "en"]
        - fields: a flat object of named text fields to translate (values may contain HTML)

        Your job: return a JSON object where each key is a target locale and each value is an object with THE SAME KEYS as the input `fields`, containing the translated text.

        Rules:
        1. Output VALID JSON ONLY. No preamble, no code fences, no trailing commentary.
        2. Preserve every HTML tag, attribute, entity, and whitespace layout inside strings. Translate only the visible text nodes.
        3. For pt_BR, use Brazilian Portuguese vocabulary (e.g. "café da manhã" not "pequeno-almoço"; "xícara" not "chávena"; "bolacha" / "biscoito" per context) while keeping the recipe structure and culinary terminology correct.
        4. For en, produce natural, editorial English suitable for a food magazine. Avoid literal word-for-word translation. Keep ingredient names recognizable (e.g. "oats", "flaxseed").
        5. When a field is `slug`, produce a slug: lowercase, ASCII only, words separated by hyphens, no trailing hyphens. Derive it from the translated title/name if possible; otherwise keep the source slug.
        6. When a field is null or empty in the source, return it as an empty string in all target locales.
        7. Never invent new fields; only translate what is given.

        Required output shape:
        {
          "<locale>": { "<field>": "<translated text>", ... },
          ...
        }
        PROMPT;
    }
}
