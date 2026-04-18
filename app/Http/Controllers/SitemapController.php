<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        foreach (config('canela.locales') as $locale => $meta) {
            $segment = trim($meta['segment'], '/');
            $prefix = $segment === '' ? '' : '/'.$segment;

            $urls[] = [
                'loc' => url($prefix === '' ? '/' : $prefix),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
            $urls[] = [
                'loc' => url($prefix.'/'.$meta['articles_path']),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
            $urls[] = [
                'loc' => url($prefix.'/'.$meta['about_path']),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];

            Article::query()
                ->where('status', ArticleStatus::Published)
                ->where('published_at', '<=', now())
                ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                ->chunk(200, function ($articles) use (&$urls, $prefix, $meta): void {
                    foreach ($articles as $article) {
                        $t = $article->translations->first();
                        if ($t === null) {
                            continue;
                        }
                        $urls[] = [
                            'loc' => url($prefix.'/'.$meta['articles_path'].'/'.$t->slug),
                            'lastmod' => $article->updated_at?->toIso8601String(),
                            'changefreq' => 'weekly',
                            'priority' => '0.7',
                        ];
                    }
                });

            Category::query()
                ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
                ->get()
                ->each(function (Category $c) use (&$urls, $prefix, $meta): void {
                    $t = $c->translations->first();
                    if ($t === null) {
                        return;
                    }
                    $urls[] = [
                        'loc' => url($prefix.'/'.$meta['categories_path'].'/'.$t->slug),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ];
                });
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
