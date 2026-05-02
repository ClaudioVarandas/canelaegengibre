<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\CategoryTranslation;
use Illuminate\Contracts\View\View;

final class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $translation = CategoryTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->firstOrFail();

        $category = $translation->category()->with('translations')->firstOrFail();

        $articles = Article::query()
            ->whereIn('category_id', $category->descendantIds())
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with([
                'translations',
                'category.translations',
                'media',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('categories.show', [
            'category' => $category,
            'translation' => $translation,
            'articles' => $articles,
        ]);
    }
}
