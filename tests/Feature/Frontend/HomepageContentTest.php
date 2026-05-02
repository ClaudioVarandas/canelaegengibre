<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;

it('renders the featured hero article title', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Receitas',
        'slug' => 'receitas',
    ]);

    $article = Article::factory()->published()->featured()->create(['category_id' => $category->id]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate sem glúten',
        'slug' => 'bolo-de-chocolate-sem-gluten',
        'excerpt' => 'Uma receita perfeita.',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Bolo de chocolate sem glúten')
        ->assertSee('Receitas');
});

it('renders a category pill and its panel', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Exercício',
        'slug' => 'exercicio',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Exercício')
        ->assertSee('x-on:click="active =', escape: false);
});

it('swaps nav brand link target when changing locale via URL prefix', function (): void {
    $this->get('/en/')->assertOk()->assertSee('data-theme="canela"', escape: false);
    expect(app()->getLocale())->toBe('en');
});
