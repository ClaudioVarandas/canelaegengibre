<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'status',
        'featured',
        'views',
        'published_at',
        'og_image_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'featured' => 'boolean',
            'views' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function translate(string $locale, bool $fallback = true): ?ArticleTranslation
    {
        $translation = $this->translations->firstWhere('locale', $locale);

        if ($translation instanceof ArticleTranslation || ! $fallback) {
            return $translation;
        }

        foreach ($this->fallbackChain($locale) as $candidate) {
            $translation = $this->translations->firstWhere('locale', $candidate);
            if ($translation instanceof ArticleTranslation) {
                return $translation;
            }
        }

        return $this->translations->first();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * @return list<string>
     */
    private function fallbackChain(string $locale): array
    {
        return match ($locale) {
            'pt_PT' => ['pt_BR', 'en'],
            'pt_BR' => ['pt_PT', 'en'],
            'en' => ['pt_PT', 'pt_BR'],
            default => ['pt_PT', 'pt_BR', 'en'],
        };
    }
}
