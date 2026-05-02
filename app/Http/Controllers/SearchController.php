<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $q = trim((string) $request->query('q', ''));

        $translations = fn ($query) => $query->where('locale', $locale);

        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['translations' => $translations])
            ->get();

        if ($q === '') {
            $results = Article::query()->whereRaw('1 = 0')->paginate(12);
        } else {
            $like = '%'.addcslashes($q, '%_\\').'%';

            $results = Article::query()
                ->where('status', ArticleStatus::Published)
                ->where('published_at', '<=', now())
                ->whereHas('translations', function ($query) use ($locale, $like): void {
                    $query->where('locale', $locale)
                        ->where(function ($w) use ($like): void {
                            $w->where('title', 'like', $like)
                                ->orWhere('excerpt', 'like', $like);
                        });
                })
                ->with([
                    'translations' => $translations,
                    'category.translations' => $translations,
                    'media',
                ])
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString();
        }

        return view('search', [
            'q' => $q,
            'results' => $results,
            'rootCategories' => $rootCategories,
        ]);
    }
}
