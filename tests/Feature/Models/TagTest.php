<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Tag;
use App\Models\TagTranslation;

it('belongs to many articles through pivot', function (): void {
    $tag = Tag::factory()->create();
    $articles = Article::factory()->count(2)->create();
    $tag->articles()->attach($articles);

    expect($tag->articles)->toHaveCount(2);
});

it('returns translation by locale', function (): void {
    $tag = Tag::factory()->create();
    TagTranslation::factory()->create([
        'tag_id' => $tag->id,
        'locale' => 'pt_PT',
        'name' => 'Açúcar',
        'slug' => 'acucar',
    ]);

    expect($tag->translate('pt_PT')->name)->toBe('Açúcar');
});
