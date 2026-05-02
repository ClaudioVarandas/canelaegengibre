<?php

declare(strict_types=1);

it('sets baseline security headers on the homepage response', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
});

it('does not set HSTS over plain http', function (): void {
    $response = $this->get('/');

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});
