<?php

declare(strict_types=1);

it('renders the homepage with the brand name', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Canela e Gengibre', escape: false);
});

it('uses the canela daisyui theme on the root element', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('data-theme="canela"', escape: false);
});
