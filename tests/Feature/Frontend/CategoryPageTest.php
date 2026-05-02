<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;

it('shows articles directly assigned to the category', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Exercício',
        'slug' => 'exercicio',
    ]);

    $article = Article::factory()->published()->create(['category_id' => $category->id]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Treino de manhã',
        'slug' => 'treino-de-manha',
    ]);

    $this->get('/categorias/exercicio')
        ->assertOk()
        ->assertSee('Treino de manhã');
});

it('aggregates articles from descendant categories on a parent', function (): void {
    $parent = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $parent->id,
        'locale' => 'pt_PT',
        'name' => 'Receitas',
        'slug' => 'receitas',
    ]);

    $child = Category::factory()->create(['parent_id' => $parent->id]);
    CategoryTranslation::factory()->create([
        'category_id' => $child->id,
        'locale' => 'pt_PT',
        'name' => 'Doces saudáveis',
        'slug' => 'doces',
    ]);

    $childArticle = Article::factory()->published()->create(['category_id' => $child->id]);
    ArticleTranslation::factory()->create([
        'article_id' => $childArticle->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate saudável',
        'slug' => 'bolo-chocolate-saudavel',
    ]);

    $this->get('/categorias/receitas')
        ->assertOk()
        ->assertSee('Bolo de chocolate saudável');
});
