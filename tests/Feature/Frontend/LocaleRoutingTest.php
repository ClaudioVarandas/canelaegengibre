<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Redirect;

it('sets the locale to pt_PT at the unprefixed root', function (): void {
    $this->get('/')->assertOk();
    expect(app()->getLocale())->toBe('pt_PT');
});

it('sets the locale to pt_BR on the /pt-br prefix', function (): void {
    $this->get('/pt-br/')->assertOk();
    expect(app()->getLocale())->toBe('pt_BR');
})->skip('pt_BR locale disabled in v1');

it('sets the locale to en on the /en prefix', function (): void {
    $this->get('/en/')->assertOk();
    expect(app()->getLocale())->toBe('en');
})->skip('en locale disabled in v1');

it('resolves a pt_PT article at /artigos/{slug}', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate',
        'slug' => 'bolo-de-chocolate',
    ]);

    $this->get('/artigos/bolo-de-chocolate')
        ->assertOk()
        ->assertSee('Bolo de chocolate');
});

it('resolves an en article at /en/articles/{slug}', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Chocolate cake',
        'slug' => 'chocolate-cake',
    ]);

    $this->get('/en/articles/chocolate-cake')
        ->assertOk()
        ->assertSee('Chocolate cake');
})->skip('en locale disabled in v1');

it('returns 404 for a slug that exists only in a different locale', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'en',
        'slug' => 'chocolate-cake',
    ]);

    $this->get('/artigos/chocolate-cake')->assertNotFound();
});

it('returns 404 for a published slug whose article is still a draft', function (): void {
    $article = Article::factory()->create([
        'status' => ArticleStatus::Draft,
        'published_at' => now()->subDay(),
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'slug' => 'draft-article',
    ]);

    $this->get('/artigos/draft-article')->assertNotFound();
});

it('resolves a pt_PT category at /categorias/{slug}', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Receitas',
        'slug' => 'receitas',
    ]);

    $this->get('/categorias/receitas')
        ->assertOk()
        ->assertSee('Receitas');
});

it('redirects a matching legacy path to the new URL with the configured code', function (): void {
    Redirect::query()->create([
        'from_path' => '/artigos/old-slug',
        'to_path' => '/artigos/new-slug',
        'code' => 301,
    ]);

    $this->get('/artigos/old-slug')->assertRedirect('/artigos/new-slug')->assertStatus(301);
});

it('returns 404 for paths that do not match any route or redirect', function (): void {
    $this->get('/this/does/not/exist')->assertNotFound();
});

it('returns 404 on /en/ root while the en locale is disabled', function (): void {
    $this->get('/en/')->assertNotFound();
    $this->get('/en/articles/anything')->assertNotFound();
});

it('returns 404 on /pt-br/ root while the pt_BR locale is disabled', function (): void {
    $this->get('/pt-br/')->assertNotFound();
    $this->get('/pt-br/artigos/anything')->assertNotFound();
});
