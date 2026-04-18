<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Tags\Pages\CreateTag;
use App\Filament\Admin\Resources\Tags\Pages\ListTags;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('lets an author list tags', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');
    $tags = Tag::factory()->count(2)->create();

    $this->actingAs($author);
    Livewire::test(ListTags::class)->assertCanSeeTableRecords($tags);
});

it('allows an author to create a tag with a pt_PT translation', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author);
    Livewire::test(CreateTag::class)
        ->fillForm([
            'translations' => [
                'pt_PT' => ['name' => 'Açúcar', 'slug' => 'acucar'],
                'pt_BR' => [],
                'en' => [],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $translation = TagTranslation::where('slug', 'acucar')->first();
    expect($translation)->not->toBeNull();
    expect($translation->name)->toBe('Açúcar');
});
