<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslations;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use HasLocaleTranslations;

    protected $fillable = [];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    protected function translationModelClass(): string
    {
        return TagTranslation::class;
    }
}
