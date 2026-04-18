<?php

declare(strict_types=1);

namespace App\Services\Translation;

interface TranslatorContract
{
    /**
     * Translate a set of source fields into one or more target locales.
     *
     * @param  string  $sourceLocale  locale tag for the source content (e.g. "pt_PT")
     * @param  array<string, ?string>  $source  field => text (HTML allowed; tags preserved)
     * @param  list<string>  $targetLocales  e.g. ["pt_BR", "en"]
     * @return array<string, array<string, string>> locale => (field => translated text)
     */
    public function translate(string $sourceLocale, array $source, array $targetLocales): array;
}
