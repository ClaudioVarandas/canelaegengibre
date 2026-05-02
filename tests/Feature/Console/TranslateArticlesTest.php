<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Services\Translation\TranslatorContract;

it('writes translations and skips records that already have all targets', function (): void {
    $partial = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $partial->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate',
        'slug' => 'bolo-de-chocolate',
        'excerpt' => 'doce',
        'content' => '<p>delicioso</p>',
    ]);

    $complete = Article::factory()->published()->create();
    foreach (['pt_PT', 'pt_BR', 'en'] as $locale) {
        ArticleTranslation::factory()->create([
            'article_id' => $complete->id,
            'locale' => $locale,
            'title' => "T-{$locale}",
            'slug' => "s-{$locale}",
        ]);
    }

    $this->app->bind(TranslatorContract::class, fn () => new class implements TranslatorContract
    {
        public function translate(string $sourceLocale, array $source, array $targetLocales): array
        {
            $out = [];
            foreach ($targetLocales as $locale) {
                $out[$locale] = [
                    'title' => '['.$locale.'] '.$source['title'],
                    'slug' => $source['slug'].'-'.strtolower(str_replace('_', '-', $locale)),
                    'excerpt' => $source['excerpt'] ?? '',
                    'content' => $source['content'] ?? '',
                    'meta_title' => '',
                    'meta_description' => '',
                ];
            }

            return $out;
        }
    });

    $this->artisan('canela:translate', ['--locales' => 'pt_BR,en'])
        ->expectsOutputToContain('Translated: 1, Skipped: 1, Failed: 0')
        ->assertSuccessful();

    expect(ArticleTranslation::where('article_id', $partial->id)->where('locale', 'pt_BR')->exists())->toBeTrue();
    expect(ArticleTranslation::where('article_id', $partial->id)->where('locale', 'en')->exists())->toBeTrue();
});

it('reports failures when the translator throws', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'X',
        'slug' => 'x',
    ]);

    $this->app->bind(TranslatorContract::class, fn () => new class implements TranslatorContract
    {
        public function translate(string $sourceLocale, array $source, array $targetLocales): array
        {
            throw new RuntimeException('upstream down');
        }
    });

    $this->artisan('canela:translate', ['--locales' => 'pt_BR'])
        ->expectsOutputToContain('upstream down')
        ->expectsOutputToContain('Translated: 0, Skipped: 0, Failed: 1')
        ->assertFailed();

    expect(ArticleTranslation::where('article_id', $article->id)->count())->toBe(1);
});

it('honors --dry-run by not persisting translations', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'X',
        'slug' => 'x',
    ]);

    $this->app->bind(TranslatorContract::class, fn () => new class implements TranslatorContract
    {
        public function translate(string $sourceLocale, array $source, array $targetLocales): array
        {
            return ['pt_BR' => ['title' => 'Y', 'slug' => 'y']];
        }
    });

    $this->artisan('canela:translate', ['--locales' => 'pt_BR', '--dry-run' => true])
        ->assertSuccessful();

    expect(ArticleTranslation::where('article_id', $article->id)->where('locale', 'pt_BR')->exists())->toBeFalse();
});
