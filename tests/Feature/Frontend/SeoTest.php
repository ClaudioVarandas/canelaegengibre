<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;

it('serves robots.txt with a Sitemap directive and admin disallow', function (): void {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())
        ->toContain('Disallow: /admin')
        ->toContain('Sitemap:')
        ->toContain('/sitemap.xml');
});

it('serves a valid XML sitemap', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'slug' => 'artigo-em-pt',
    ]);

    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/xml');
    expect($response->getContent())
        ->toContain('<urlset')
        ->toContain('artigo-em-pt');
});

it('emits per-locale meta tags and Article JSON-LD on article show', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Aveia',
        'slug' => 'aveia',
        'excerpt' => 'Cereal saciante',
        'meta_title' => 'Aveia | SEO',
        'meta_description' => 'Tudo sobre aveia.',
    ]);

    $response = $this->get('/artigos/aveia');
    $response->assertOk();
    expect($response->getContent())
        ->toContain('Aveia | SEO')
        ->toContain('<meta name="description" content="Tudo sobre aveia."')
        ->toContain('<meta property="og:type" content="article"')
        ->toContain('"@type":"Article"')
        ->toContain('"headline":"Aveia"');
});

it('emits hreflang alternates on every rendered page', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())->toContain('hreflang="pt-pt"');
});

it('omits hreflang for disabled locales', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())
        ->not->toContain('hreflang="pt-br"')
        ->not->toContain('hreflang="en"');
});
