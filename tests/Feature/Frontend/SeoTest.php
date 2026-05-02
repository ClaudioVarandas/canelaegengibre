<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Tag;
use App\Models\TagTranslation;

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

it('emits an x-default hreflang on the homepage', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())->toContain('hreflang="x-default"');
});

it('emits WebSite + Organization JSON-LD on the homepage', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())
        ->toContain('"@type":"WebSite"')
        ->toContain('"@type":"Organization"')
        ->toContain('"SearchAction"');
});

it('emits og:site_name and twitter title/description on every page', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())
        ->toContain('property="og:site_name" content="Canela e Gengibre"')
        ->toContain('name="twitter:title"')
        ->toContain('name="twitter:description"');
});

it('marks search results as noindex,follow', function (): void {
    $response = $this->get('/pesquisa');
    $response->assertOk();
    expect($response->getContent())->toContain('name="robots" content="noindex, follow"');
});

it('marks tag pages as noindex,follow', function (): void {
    $tag = Tag::factory()->create();
    TagTranslation::factory()->create([
        'tag_id' => $tag->id,
        'locale' => 'pt_PT',
        'name' => 'doces',
        'slug' => 'doces',
    ]);

    $response = $this->get('/tag/doces');
    $response->assertOk();
    expect($response->getContent())->toContain('name="robots" content="noindex, follow"');
});

it('does not emit a robots meta on indexable pages', function (): void {
    $response = $this->get('/');
    $response->assertOk();
    expect($response->getContent())->not->toContain('name="robots"');
});

it('emits Breadcrumb + enriched Article JSON-LD on article show', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Receitas',
        'slug' => 'receitas',
    ]);

    $tag = Tag::factory()->create();
    TagTranslation::factory()->create([
        'tag_id' => $tag->id,
        'locale' => 'pt_PT',
        'name' => 'aveia',
        'slug' => 'aveia',
    ]);

    $article = Article::factory()->published()->create(['category_id' => $category->id]);
    $article->tags()->attach($tag);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Bolo de aveia',
        'slug' => 'bolo-de-aveia',
    ]);

    $response = $this->get('/artigos/bolo-de-aveia');
    $response->assertOk();
    expect($response->getContent())
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"@type":"ListItem"')
        ->toContain('"name":"Receitas"')
        ->toContain('"mainEntityOfPage"')
        ->toContain('"publisher"')
        ->toContain('"articleSection":"Receitas"')
        ->toContain('"keywords":"aveia"');
});

it('preserves the page query string in the canonical URL', function (): void {
    foreach (range(1, 13) as $i) {
        $article = Article::factory()->published()->create();
        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'locale' => 'pt_PT',
            'title' => "Artigo {$i}",
            'slug' => "artigo-{$i}",
        ]);
    }

    $response = $this->get('/artigos?page=2');
    $response->assertOk();
    expect($response->getContent())->toContain('rel="canonical" href="'.url('/artigos').'?page=2"');
});

it('includes lastmod for the home and articles index in the sitemap', function (): void {
    $article = Article::factory()->published()->create();
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'X',
        'slug' => 'x',
    ]);

    $response = $this->get('/sitemap.xml');
    $response->assertOk();
    expect($response->getContent())->toContain('<lastmod>');
});
