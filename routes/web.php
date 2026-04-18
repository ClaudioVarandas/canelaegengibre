<?php

declare(strict_types=1);

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Models\Redirect as LegacyRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $body = "User-agent: *\nDisallow: /admin\nSitemap: ".url('/sitemap.xml')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

foreach (config('canela.locales') as $locale => $meta) {
    $segment = $meta['segment'];
    $articlesPath = $meta['articles_path'];
    $categoriesPath = $meta['categories_path'];
    $aboutPath = $meta['about_path'];

    Route::middleware('set-locale:'.$locale)
        ->prefix($segment)
        ->name(str_replace('_', '-', strtolower($locale)).'.')
        ->group(function () use ($articlesPath, $categoriesPath, $aboutPath): void {
            Route::get('/', [HomeController::class, 'index'])->name('home');
            Route::get($articlesPath, [ArticleController::class, 'index'])->name('articles.index');
            Route::get($articlesPath.'/{slug}', [ArticleController::class, 'show'])->name('articles.show');
            Route::get($categoriesPath.'/{slug}', [CategoryController::class, 'show'])->name('categories.show');
            Route::get($aboutPath, [AboutController::class, 'index'])->name('about');
        });
}

Route::fallback(function (Request $request) {
    $path = '/'.ltrim($request->path(), '/');

    $redirect = LegacyRedirect::query()->where('from_path', $path)->first();
    if ($redirect !== null) {
        return redirect($redirect->to_path, $redirect->code);
    }

    abort(404);
});
