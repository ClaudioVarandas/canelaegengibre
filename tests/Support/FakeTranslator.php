<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Translation\TranslatorContract;
use Illuminate\Support\Str;

class FakeTranslator implements TranslatorContract
{
    /**
     * @var list<array{sourceLocale: string, source: array<string, ?string>, targetLocales: list<string>}>
     */
    public array $calls = [];

    /**
     * {@inheritDoc}
     */
    public function translate(string $sourceLocale, array $source, array $targetLocales): array
    {
        $this->calls[] = [
            'sourceLocale' => $sourceLocale,
            'source' => $source,
            'targetLocales' => $targetLocales,
        ];

        $out = [];
        foreach ($targetLocales as $locale) {
            $localeRow = [];
            foreach ($source as $field => $value) {
                if ($value === null) {
                    $localeRow[$field] = '';

                    continue;
                }
                if ($field === 'slug') {
                    $localeRow[$field] = Str::slug($value).'-'.strtolower(str_replace('_', '-', $locale));

                    continue;
                }
                $localeRow[$field] = '['.$locale.'] '.$value;
            }
            $out[$locale] = $localeRow;
        }

        return $out;
    }
}
