<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Categories\Pages\CreateCategory;
use App\Filament\Admin\Resources\Categories\Pages\EditCategory;
use App\Filament\Admin\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('lets a super-admin list categories', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $categories = Category::factory()->count(3)->create();

    $this->actingAs($admin);
    Livewire::test(ListCategories::class)->assertCanSeeTableRecords($categories);
});

it('forbids an author from creating categories', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author);
    Livewire::test(CreateCategory::class)->assertForbidden();
});

it('creates a category and persists its pt_PT translation', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin);
    Livewire::test(CreateCategory::class)
        ->fillForm([
            'parent_id' => null,
            'sort' => 5,
            'translations' => [
                'pt_PT' => ['name' => 'Receitas', 'slug' => 'receitas', 'description' => null],
                'pt_BR' => [],
                'en' => [],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $translation = CategoryTranslation::where('slug', 'receitas')->first();
    expect($translation)->not->toBeNull();
    expect($translation->name)->toBe('Receitas');
});

it('loads existing translations when editing', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'en',
        'name' => 'Recipes',
        'slug' => 'recipes',
    ]);

    $this->actingAs($admin);
    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertFormSet(['translations.en.name' => 'Recipes']);
});
