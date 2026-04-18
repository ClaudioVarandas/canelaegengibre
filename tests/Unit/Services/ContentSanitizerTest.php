<?php

declare(strict_types=1);

use App\Services\Import\ContentSanitizer;

it('decodes html entities', function (): void {
    $sanitizer = new ContentSanitizer;

    expect($sanitizer->sanitize('&lt;p&gt;Olá&lt;/p&gt;'))->toBe('<p>Olá</p>');
});

it('rewrites legacy absolute URLs to site-relative paths', function (): void {
    $sanitizer = new ContentSanitizer;

    $html = '<img src="http://canelaegengibre.pt/uploads/media/images/foo.jpg">';
    expect($sanitizer->sanitize($html))->toBe('<img src="/uploads/media/images/foo.jpg">');
});

it('handles the www. variant of the legacy host', function (): void {
    $sanitizer = new ContentSanitizer;

    $html = '<a href="https://www.canelaegengibre.pt/artigo">Link</a>';
    expect($sanitizer->sanitize($html))->toBe('<a href="/artigo">Link</a>');
});

it('extracts unique upload paths referenced by img and a tags', function (): void {
    $sanitizer = new ContentSanitizer;

    $html = '<img src="/uploads/media/images/a.jpg"><img src="http://canelaegengibre.pt/uploads/media/images/a.jpg"><a href="/uploads/docs/file.pdf">doc</a>';
    expect($sanitizer->extractUploadPaths($html))->toBe([
        'uploads/media/images/a.jpg',
        'uploads/docs/file.pdf',
    ]);
});
