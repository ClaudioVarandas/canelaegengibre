<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleTranslation;

it('renders the empty state when no query is provided', function (): void {
    $this->get('/pesquisa')
        ->assertOk()
        ->assertSee('Pesquisar artigos')
        ->assertSee('Escreve um termo acima para encontrar artigos.');
});

it('rejects queries shorter than the min length with a hint', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Tarte rústica',
        'slug' => 'tarte-rustica',
        'excerpt' => 'fácil',
    ]);

    $this->get('/pesquisa?q=ta')
        ->assertOk()
        ->assertDontSee('Tarte rústica')
        ->assertSee('Indica pelo menos 3 caracteres.');
});

it('caps query length to prevent abuse', function (): void {
    $long = str_repeat('a', 200);

    $this->get('/pesquisa?q='.$long)
        ->assertOk();
});

it('matches an article by translated title', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de chocolate sem glúten',
        'slug' => 'bolo-de-chocolate',
        'excerpt' => 'Receita simples.',
    ]);

    $this->get('/pesquisa?q=chocolate')
        ->assertOk()
        ->assertSee('Bolo de chocolate sem glúten');
});

it('matches an article by translated excerpt', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Tarte de maçã',
        'slug' => 'tarte-de-maca',
        'excerpt' => 'Outono em forma de tarte.',
    ]);

    $this->get('/pesquisa?q=outono')
        ->assertOk()
        ->assertSee('Tarte de maçã');
});

it('excludes drafts', function (): void {
    $article = Article::factory()->create([
        'status' => ArticleStatus::Draft,
        'published_at' => null,
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Esboço sobre chocolate',
        'slug' => 'esboco-chocolate',
        'excerpt' => 'rascunho',
    ]);

    $this->get('/pesquisa?q=chocolate')
        ->assertOk()
        ->assertDontSee('Esboço sobre chocolate')
        ->assertSee('Sem resultados', escape: false);
});

it('excludes future-dated articles', function (): void {
    $article = Article::factory()->create([
        'status' => ArticleStatus::Published,
        'published_at' => now()->addWeek(),
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Futuro chocolate',
        'slug' => 'futuro-chocolate',
        'excerpt' => 'agendado',
    ]);

    $this->get('/pesquisa?q=chocolate')
        ->assertOk()
        ->assertDontSee('Futuro chocolate');
});

it('paginates results at 12 per page', function (): void {
    for ($i = 0; $i < 13; $i++) {
        $article = Article::factory()->published()->create();
        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'locale' => 'pt_PT',
            'title' => "Receita de chocolate {$i}",
            'slug' => "chocolate-{$i}",
            'excerpt' => 'doce',
        ]);
    }

    $response = $this->get('/pesquisa?q=chocolate');
    $response->assertOk();

    $body = $response->getContent();
    $visible = 0;
    for ($i = 0; $i < 13; $i++) {
        if (str_contains($body, "Receita de chocolate {$i}")) {
            $visible++;
        }
    }
    expect($visible)->toBe(12);
});

it('serves the en locale search route', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'en',
        'title' => 'Gluten-free chocolate cake',
        'slug' => 'gluten-free-chocolate-cake',
        'excerpt' => 'simple recipe',
    ]);

    $this->get('/en/search?q=chocolate')
        ->assertOk()
        ->assertSee('Gluten-free chocolate cake');
})->skip('en locale disabled in v1');

it('serves the pt_BR locale search route', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_BR',
        'title' => 'Bolo de chocolate brasileiro',
        'slug' => 'bolo-de-chocolate-br',
        'excerpt' => 'doce',
    ]);

    $this->get('/pt-br/busca?q=chocolate')
        ->assertOk()
        ->assertSee('Bolo de chocolate brasileiro');
})->skip('pt_BR locale disabled in v1');
