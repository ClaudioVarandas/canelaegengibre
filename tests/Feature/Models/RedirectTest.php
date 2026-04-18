<?php

declare(strict_types=1);

use App\Models\Redirect;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces unique from_path', function (): void {
    Redirect::factory()->create(['from_path' => '/artigos/old']);

    expect(fn () => Redirect::factory()->create(['from_path' => '/artigos/old']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('defaults to HTTP 301', function (): void {
    $redirect = Redirect::factory()->create();

    expect($redirect->fresh()->code)->toBe(301);
});
