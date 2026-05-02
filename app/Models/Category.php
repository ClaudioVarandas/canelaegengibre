<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'sort'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translate(string $locale): ?CategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->first();
    }

    /**
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        $ids = collect([$this->id]);
        $frontier = [$this->id];

        while ($frontier !== []) {
            $next = self::query()->whereIn('parent_id', $frontier)->pluck('id');
            if ($next->isEmpty()) {
                break;
            }
            $ids = $ids->concat($next);
            $frontier = $next->all();
        }

        return $ids;
    }
}
