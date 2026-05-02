<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

final class HomeController extends Controller
{
    public function index(): View
    {
        $enabled = array_keys(enabled_locales());
        $localeFilter = fn ($q) => $q->whereIn('locale', $enabled);

        $articleEager = [
            'translations' => $localeFilter,
            'category.translations' => $localeFilter,
            'media',
        ];

        $heroMain = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with($articleEager)
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->first();

        $heroRight = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->when($heroMain, fn ($q) => $q->whereKeyNot($heroMain->id))
            ->with($articleEager)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['translations' => $localeFilter])
            ->get();

        $categoryArticles = $rootCategories->mapWithKeys(function (Category $category) use ($articleEager): array {
            $articles = Article::query()
                ->whereIn('category_id', $category->descendantIds())
                ->where('status', ArticleStatus::Published)
                ->where('published_at', '<=', now())
                ->with($articleEager)
                ->orderByDesc('published_at')
                ->limit(4)
                ->get();

            $translation = $category->translate(app()->getLocale());

            return [$category->id => [
                'category' => $category,
                'translation' => $translation,
                'articles' => $articles,
            ]];
        });

        $carousel = Article::query()
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->with(['translations' => $localeFilter, 'category.translations' => $localeFilter, 'media'])
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        return view('home', [
            'heroMain' => $heroMain,
            'heroRight' => $heroRight,
            'rootCategories' => $rootCategories,
            'categoryArticles' => $categoryArticles,
            'carousel' => $carousel,
        ]);
    }
}
