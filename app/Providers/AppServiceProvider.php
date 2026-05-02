<?php

declare(strict_types=1);

namespace App\Providers;

use Anthropic\Client as AnthropicClient;
use App\Models\Category;
use App\Models\User;
use App\Services\Translation\ClaudeTranslator;
use App\Services\Translation\TranslatorContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnthropicClient::class, function () {
            $apiKey = (string) config('services.anthropic.api_key');

            return new AnthropicClient(apiKey: $apiKey);
        });

        $this->app->singleton(TranslatorContract::class, function ($app) {
            return new ClaudeTranslator(
                client: $app->make(AnthropicClient::class),
                model: (string) config('services.anthropic.model', 'claude-opus-4-7'),
            );
        });
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.canela');

        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        View::composer([
            'home',
            'about',
            'search',
            'articles.index',
            'articles.show',
            'categories.show',
            'tags.show',
        ], function ($view): void {
            if ($view->offsetExists('rootCategories')) {
                return;
            }
            $locale = app()->getLocale();
            $localeFilter = fn ($q) => $q->where('locale', $locale);
            $view->with('rootCategories', Category::query()
                ->whereNull('parent_id')
                ->with([
                    'translations' => $localeFilter,
                    'children' => fn ($q) => $q->orderBy('sort')->with(['translations' => $localeFilter]),
                ])
                ->orderBy('sort')
                ->get());
        });
    }
}
