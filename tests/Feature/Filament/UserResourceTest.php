<?php

declare(strict_types=1);

use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('lets a super-admin list users', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin);
    Livewire::test(ListUsers::class)->assertOk();
});

it('forbids an author from listing users', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author);
    Livewire::test(ListUsers::class)->assertForbidden();
});

it('creates a user with an author role and hashed password', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $authorRoleId = Role::findByName('author', 'web')->id;

    $this->actingAs($admin);
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Carla Cerqueira',
            'email' => 'carla@example.com',
            'password' => 'secretpass',
            'roles' => [$authorRoleId],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'carla@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('author'))->toBeTrue();
    expect(password_verify('secretpass', $user->password))->toBeTrue();
});

it('keeps the existing password when password field is blank on edit', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $target = User::factory()->create();
    $originalHash = $target->password;

    $this->actingAs($admin);
    Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
        ->fillForm(['name' => 'Updated Name', 'password' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($target->fresh()->password)->toBe($originalHash);
});

it('exposes the roles select to super-admin editors', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $target = User::factory()->create();
    $target->assignRole('author');

    $this->actingAs($admin);
    Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
        ->assertFormFieldIsVisible('roles');
});
