<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\CategoryTranslation;

it('exposes a self-referential parent and children relationship', function (): void {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent->is($parent))->toBeTrue();
    expect($parent->children)->toHaveCount(1);
});

it('has many articles', function (): void {
    $category = Category::factory()->create();
    Article::factory()->count(2)->create(['category_id' => $category->id]);

    expect($category->articles)->toHaveCount(2);
});

it('returns translation by locale', function (): void {
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Receitas',
        'slug' => 'receitas',
    ]);

    expect($category->translate('pt_PT')->name)->toBe('Receitas');
});
