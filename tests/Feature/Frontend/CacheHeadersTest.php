<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;

it('emits public Cache-Control headers on homepage for guests', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=600');
    expect($response->headers->get('Vary'))
        ->toContain('Accept-Language');
});

it('does not cache responses for authenticated users', function (): void {
    $this->seed(RoleSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('author');

    $response = $this->actingAs($user)->get('/');

    $cacheControl = (string) $response->headers->get('Cache-Control');
    expect($cacheControl)->not->toContain('public');
});

it('emits a long TTL on the sitemap', function (): void {
    $response = $this->get('/sitemap.xml');
    expect($response->headers->get('Cache-Control'))->toContain('max-age=3600');
});
