<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Tag;
use App\Models\TagTranslation;

it('renders articles tagged with the slug', function (): void {
    $tag = Tag::factory()->create();
    TagTranslation::factory()->create([
        'tag_id' => $tag->id,
        'locale' => 'pt_PT',
        'name' => 'Vegetariano',
        'slug' => 'vegetariano',
    ]);

    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolonhesa de lentilhas',
        'slug' => 'bolonhesa-de-lentilhas',
        'excerpt' => 'rápido e simples',
    ]);
    $article->tags()->attach($tag);

    $this->get('/tag/vegetariano')
        ->assertOk()
        ->assertSee('#Vegetariano')
        ->assertSee('Bolonhesa de lentilhas');
});

it('returns 404 when the tag slug does not exist', function (): void {
    $this->get('/tag/inexistente')->assertNotFound();
});

it('shows an empty state when no published articles use the tag', function (): void {
    $tag = Tag::factory()->create();
    TagTranslation::factory()->create([
        'tag_id' => $tag->id,
        'locale' => 'pt_PT',
        'name' => 'Solo',
        'slug' => 'solo',
    ]);

    $this->get('/tag/solo')
        ->assertOk()
        ->assertSee('#Solo')
        ->assertSee('Ainda não há artigos com esta etiqueta.');
});
