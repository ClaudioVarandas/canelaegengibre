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
    private const MIN_QUERY_LENGTH = 3;

    private const MAX_QUERY_LENGTH = 100;

    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $raw = trim((string) $request->query('q', ''));
        $q = mb_substr($raw, 0, self::MAX_QUERY_LENGTH);

        $translations = fn ($query) => $query->where('locale', $locale);

        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->with(['translations' => $translations])
            ->get();

        $tooShort = $q !== '' && mb_strlen($q) < self::MIN_QUERY_LENGTH;

        if ($q === '' || $tooShort) {
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
            'tooShort' => $tooShort,
            'minLength' => self::MIN_QUERY_LENGTH,
        ]);
    }
}
