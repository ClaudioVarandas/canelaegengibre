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
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('content');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 600, 450)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('hero')
            ->fit(Fit::Crop, 1600, 900)
            ->format('webp')
            ->nonQueued();
    }

    public function coverUrl(string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia('cover');
        if ($media === null) {
            return null;
        }

        return $conversion !== '' && $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }

    /**
     * @return array{src: string, srcset: string, sizes: string}|null
     */
    public function coverImageAttrs(string $sizes = '(min-width: 1024px) 50vw, 100vw'): ?array
    {
        $media = $this->getFirstMedia('cover');
        if ($media === null) {
            return null;
        }

        $original = $media->getUrl();
        $thumb = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null;
        $hero = $media->hasGeneratedConversion('hero') ? $media->getUrl('hero') : null;

        $srcset = collect([
            $thumb ? "{$thumb} 600w" : null,
            $hero ? "{$hero} 1600w" : null,
        ])->filter()->implode(', ');

        return [
            'src' => $hero ?? $original,
            'srcset' => $srcset,
            'sizes' => $sizes,
        ];
    }

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
