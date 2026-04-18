<?php

declare(strict_types=1);

namespace App\Filament\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;

trait SyncsTranslations
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $pendingTranslations = [];

    /**
     * @return list<string>
     */
    abstract protected function translationAttributes(): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractTranslations($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractTranslations($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['translations'] = $record->translations
            ->mapWithKeys(fn (Model $t) => [$t->getAttribute('locale') => $t->only($this->translationAttributes())])
            ->all();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->persistTranslations();
    }

    protected function afterSave(): void
    {
        $this->persistTranslations();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extractTranslations(array $data): array
    {
        $this->pendingTranslations = $data['translations'] ?? [];
        unset($data['translations']);

        return $data;
    }

    private function persistTranslations(): void
    {
        $record = $this->record;
        $identifier = collect($this->translationAttributes())
            ->first(fn (string $attr) => in_array($attr, ['title', 'name'], true)) ?? 'name';

        foreach ($this->pendingTranslations as $locale => $attrs) {
            if (blank($attrs[$identifier] ?? null)) {
                $record->translations()->where('locale', $locale)->delete();

                continue;
            }
            $record->translations()->updateOrCreate(['locale' => $locale], $attrs);
        }
    }
}
