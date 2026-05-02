<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;

beforeEach(function (): void {
    $category = Category::factory()->create();

    foreach (['pt_PT', 'pt_BR', 'en'] as $locale) {
        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'locale' => $locale,
            'name' => match ($locale) {
                'pt_PT' => 'Receitas',
                'pt_BR' => 'Receitas BR',
                'en' => 'Recipes',
            },
            'slug' => match ($locale) {
                'pt_PT' => 'receitas',
                'pt_BR' => 'receitas-br',
                'en' => 'recipes',
            },
        ]);
    }

    $article = Article::factory()->published()->featured()->create([
        'category_id' => $category->id,
    ]);

    foreach (['pt_PT' => 'Bolo de chocolate', 'pt_BR' => 'Bolo de chocolate BR', 'en' => 'Chocolate cake'] as $locale => $title) {
        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'locale' => $locale,
            'title' => $title,
            'slug' => match ($locale) {
                'pt_PT' => 'bolo-de-chocolate',
                'pt_BR' => 'bolo-de-chocolate-br',
                'en' => 'chocolate-cake',
            },
            'excerpt' => 'Editorial excerpt for '.$locale,
            'content' => '<p>Content for '.$locale.'</p>',
        ]);
    }
});

it('smoke-tests every key frontend route for the active locale', function (): void {
    $routes = [
        '/',
        '/artigos',
        '/artigos/bolo-de-chocolate',
        '/categorias/receitas',
        '/sobre',
        '/pesquisa',
        '/sitemap.xml',
        '/robots.txt',
    ];

    visit($routes)->assertNoSmoke();
});

it('opens an article from the homepage hero and reads the article title', function (): void {
    visit('/')
        ->assertSee('Bolo de chocolate')
        ->click('Bolo de chocolate')
        ->assertPathIs('/artigos/bolo-de-chocolate')
        ->assertSee('Bolo de chocolate')
        ->assertNoJavascriptErrors();
});
