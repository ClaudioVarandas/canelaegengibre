<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        $translations = fn ($q) => $q->where('locale', $locale);

        $heroMain = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->where('featured', true)
            ->with(['translations' => $translations, 'category.translations' => $translations, 'media'])
            ->orderByDesc('published_at')
            ->first();

        $heroRight = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->when($heroMain, fn ($q) => $q->whereKeyNot($heroMain->id))
            ->with(['translations' => $translations, 'category.translations' => $translations, 'media'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['translations' => $translations])
            ->get();

        $categoryArticles = $rootCategories->mapWithKeys(function (Category $category) use ($translations, $locale): array {
            $articles = $category->articles()
                ->where('status', ArticleStatus::Published)
                ->where('published_at', '<=', now())
                ->with(['translations' => $translations, 'media'])
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();

            $translation = $category->translate($locale);

            return [$category->id => [
                'category' => $category,
                'translation' => $translation,
                'articles' => $articles,
            ]];
        });

        $carousel = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with(['translations' => $translations, 'media'])
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        $editorial = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->where('featured', true)
            ->when($heroMain, fn ($q) => $q->whereKeyNot($heroMain->id))
            ->with(['translations' => $translations, 'media'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $editorialMain = $editorial->first();
        $editorialSide = $editorial->skip(1)->values();

        $tags = Tag::query()
            ->with(['translations' => $translations])
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->limit(16)
            ->get();

        return view('home', [
            'heroMain' => $heroMain,
            'heroRight' => $heroRight,
            'rootCategories' => $rootCategories,
            'categoryArticles' => $categoryArticles,
            'carousel' => $carousel,
            'editorialMain' => $editorialMain,
            'editorialSide' => $editorialSide,
            'tags' => $tags,
        ]);
    }
}
