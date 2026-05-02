<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\TagTranslation;
use Illuminate\Contracts\View\View;

final class TagController extends Controller
{
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $translation = TagTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->firstOrFail();

        $tag = $translation->tag()->with('translations')->firstOrFail();

        $articles = $tag->articles()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with([
                'translations',
                'category.translations',
                'media',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('tags.show', [
            'tag' => $tag,
            'translation' => $translation,
            'articles' => $articles,
        ]);
    }
}
