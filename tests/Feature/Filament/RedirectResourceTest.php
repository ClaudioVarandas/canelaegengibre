<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Admin\Resources\Redirects\Pages\ListRedirects;
use App\Models\Redirect;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('forbids an author from viewing redirects', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author);
    Livewire::test(ListRedirects::class)->assertForbidden();
});

it('creates a redirect with default 301 status code', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin);
    Livewire::test(CreateRedirect::class)
        ->fillForm([
            'from_path' => '/artigos/old-slug',
            'to_path' => '/artigos/new-slug',
            'code' => 301,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $redirect = Redirect::where('from_path', '/artigos/old-slug')->first();
    expect($redirect)->not->toBeNull();
    expect($redirect->code)->toBe(301);
});

it('rejects a duplicate from_path', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    Redirect::factory()->create(['from_path' => '/artigos/dup']);

    $this->actingAs($admin);
    Livewire::test(CreateRedirect::class)
        ->fillForm([
            'from_path' => '/artigos/dup',
            'to_path' => '/artigos/other',
            'code' => 301,
        ])
        ->call('create')
        ->assertHasFormErrors(['from_path']);
});
