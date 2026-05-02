<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasLocaleTranslations
{
    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModelClass());
    }

    public function translate(string $locale): ?Model
    {
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->first();
    }

    /**
     * @return class-string<Model>
     */
    abstract protected function translationModelClass(): string;
}
