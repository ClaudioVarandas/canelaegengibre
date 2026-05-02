<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;

it('renders the homepage with the brand name', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Canela e Gengibre', escape: false);
});

it('uses the canela daisyui theme on the root element', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('data-theme="canela"', escape: false);
});

it('renders the en homepage when articles only have a pt_PT translation', function (): void {
    $article = Article::factory()->published()->featured()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate',
        'slug' => 'bolo-de-chocolate',
        'excerpt' => 'só PT',
    ]);

    $this->get('/en/')->assertOk();
});
