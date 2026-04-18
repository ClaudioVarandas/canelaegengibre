<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

it('belongs to an author and a category', function (): void {
    $article = Article::factory()->create();

    expect($article->author)->toBeInstanceOf(User::class);
    expect($article->category)->toBeInstanceOf(Category::class);
});

it('attaches many tags', function (): void {
    $article = Article::factory()->create();
    $article->tags()->attach(Tag::factory()->count(3)->create());

    expect($article->tags)->toHaveCount(3);
});

it('has translations keyed by locale', function (): void {
    $article = Article::factory()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate',
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Chocolate cake',
    ]);

    expect($article->translate('pt_PT')->title)->toBe('Bolo de chocolate');
    expect($article->translate('en')->title)->toBe('Chocolate cake');
});

it('falls back across locales when requested translation is missing', function (): void {
    $article = Article::factory()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Só em PT',
    ]);

    expect($article->translate('en')->title)->toBe('Só em PT');
    expect($article->translate('pt_BR')->title)->toBe('Só em PT');
});

it('casts status to the ArticleStatus enum', function (): void {
    $article = Article::factory()->create(['status' => ArticleStatus::Published]);

    expect($article->fresh()->status)->toBe(ArticleStatus::Published);
});

it('published scope returns only published articles with past publication date', function (): void {
    Article::factory()->create();
    Article::factory()->published()->create();
    Article::factory()->create([
        'status' => ArticleStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    expect(Article::published()->count())->toBe(1);
});

it('featured scope filters by featured flag', function (): void {
    Article::factory()->count(2)->create();
    Article::factory()->featured()->create();

    expect(Article::featured()->count())->toBe(1);
});
