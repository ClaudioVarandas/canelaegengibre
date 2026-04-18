<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Contracts\View\View;

final class ArticleController extends Controller
{
    public function index(): View
    {
        $articles = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with([
                'translations' => fn ($q) => $q->where('locale', app()->getLocale()),
                'category.translations',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('articles.index', ['articles' => $articles]);
    }

    public function show(string $slug): View
    {
        $translation = ArticleTranslation::query()
            ->where('locale', app()->getLocale())
            ->where('slug', $slug)
            ->firstOrFail();

        $article = $translation->article()->with(['author', 'category.translations', 'tags.translations'])->firstOrFail();

        if ($article->status !== ArticleStatus::Published) {
            abort(404);
        }

        return view('articles.show', ['article' => $article, 'translation' => $translation]);
    }
}
