<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Pages;

use App\Filament\Admin\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $pendingTranslations = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingTranslations = $data['translations'] ?? [];
        unset($data['translations']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Article $record */
        $record = $this->record;

        foreach ($this->pendingTranslations as $locale => $attrs) {
            if (blank($attrs['title'] ?? null)) {
                continue;
            }
            $record->translations()->updateOrCreate(
                ['locale' => $locale],
                $attrs,
            );
        }
    }
}
