<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Services\Translation\TranslatorContract;
use Tests\Support\FakeTranslator;

beforeEach(function (): void {
    $this->fake = new FakeTranslator;
    $this->app->instance(TranslatorContract::class, $this->fake);
});

it('translates pt_PT articles into pt_BR and en and persists translations', function (): void {
    $article = Article::factory()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate',
        'slug' => 'bolo-de-chocolate',
        'excerpt' => 'Receita simples',
        'content' => '<p>Misture tudo.</p>',
    ]);

    $this->artisan('canela:translate', [
        '--entity' => 'articles',
        '--locales' => 'pt_BR,en',
    ])->assertSuccessful();

    expect(ArticleTranslation::where('article_id', $article->id)->where('locale', 'pt_BR')->first())
        ->not->toBeNull()
        ->and(ArticleTranslation::where('article_id', $article->id)->where('locale', 'pt_BR')->first()->title)
        ->toBe('[pt_BR] Bolo de chocolate');

    $en = ArticleTranslation::where('article_id', $article->id)->where('locale', 'en')->first();
    expect($en)->not->toBeNull();
    expect($en->slug)->toBe('bolo-de-chocolate-en');
    expect($en->content)->toBe('[en] <p>Misture tudo.</p>');

    expect($this->fake->calls)->toHaveCount(1);
    expect($this->fake->calls[0]['targetLocales'])->toBe(['pt_BR', 'en']);
});

it('skips articles that already have all target translations', function (): void {
    $article = Article::factory()->create();
    foreach (['pt_PT', 'pt_BR', 'en'] as $locale) {
        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'locale' => $locale,
        ]);
    }

    $this->artisan('canela:translate', ['--entity' => 'articles'])->assertSuccessful();

    expect($this->fake->calls)->toBeEmpty();
});

it('only calls the translator for missing locales', function (): void {
    $article = Article::factory()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Aveia',
        'slug' => 'aveia',
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_BR',
    ]);

    $this->artisan('canela:translate', ['--entity' => 'articles'])->assertSuccessful();

    expect($this->fake->calls)->toHaveCount(1);
    expect($this->fake->calls[0]['targetLocales'])->toBe(['en']);
});

it('dry-run does not persist any translations', function (): void {
    $article = Article::factory()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Aveia',
        'slug' => 'aveia',
    ]);

    $this->artisan('canela:translate', ['--entity' => 'articles', '--dry-run' => true])->assertSuccessful();

    expect(ArticleTranslation::where('article_id', $article->id)->count())->toBe(1);
});
