<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;

it('strips script tags from rendered article content', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Receita maliciosa',
        'slug' => 'receita-maliciosa',
        'excerpt' => 'sem perigo',
        'content' => '<p>conteúdo seguro</p><script>window.pwned = true;</script><p>fim</p>',
    ]);

    $response = $this->get('/artigos/receita-maliciosa');

    $response->assertOk();
    $response->assertSee('conteúdo seguro');
    $response->assertSee('fim');
    $response->assertDontSee('<script>', escape: false);
    $response->assertDontSee('window.pwned', escape: false);
});

it('strips inline event handlers from rendered article content', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Outra maliciosa',
        'slug' => 'outra-maliciosa',
        'excerpt' => 'sem perigo',
        'content' => '<p onclick="alert(1)">clique</p><a href="javascript:alert(1)">link</a>',
    ]);

    $response = $this->get('/artigos/outra-maliciosa');

    $response->assertOk();
    $response->assertSee('clique');
    $response->assertDontSee('onclick=', escape: false);
    $response->assertDontSee('javascript:', escape: false);
});
