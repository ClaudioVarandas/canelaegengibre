<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

it('redirects guests to the admin login page', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('forbids authenticated users without a role from accessing the panel', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows super-admin users to access the panel', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('allows author users to access the panel', function (): void {
    $author = User::factory()->create();
    $author->assignRole('author');

    $this->actingAs($author)->get('/admin')->assertOk();
});
