<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasLocaleTranslations;

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

    protected function translationModelClass(): string
    {
        return CategoryTranslation::class;
    }

    /** @var Collection<int, int>|null */
    private ?Collection $descendantIdsCache = null;

    /**
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        if ($this->descendantIdsCache !== null) {
            return $this->descendantIdsCache;
        }

        $rows = DB::select(<<<'SQL'
            WITH RECURSIVE cat_tree AS (
                SELECT id FROM categories WHERE id = ?
                UNION ALL
                SELECT c.id FROM categories c
                INNER JOIN cat_tree t ON c.parent_id = t.id
            )
            SELECT id FROM cat_tree
        SQL, [$this->id]);

        return $this->descendantIdsCache = collect($rows)->pluck('id');
    }
}
