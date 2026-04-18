<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Pages;

use App\Filament\Admin\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $pendingTranslations = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Article $record */
        $record = $this->getRecord();

        $data['translations'] = $record->translations
            ->mapWithKeys(fn ($t) => [$t->locale => $t->only([
                'title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description',
            ])])
            ->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingTranslations = $data['translations'] ?? [];
        unset($data['translations']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Article $record */
        $record = $this->record;

        foreach ($this->pendingTranslations as $locale => $attrs) {
            if (blank($attrs['title'] ?? null)) {
                $record->translations()->where('locale', $locale)->delete();

                continue;
            }
            $record->translations()->updateOrCreate(
                ['locale' => $locale],
                $attrs,
            );
        }
    }
}
