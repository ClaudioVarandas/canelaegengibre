<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;

it('maps legacy state integers to statuses', function (int $legacy, ArticleStatus $expected): void {
    expect(ArticleStatus::fromLegacyState($legacy))->toBe($expected);
})->with([
    [-1, ArticleStatus::Draft],
    [0, ArticleStatus::Draft],
    [1, ArticleStatus::Published],
    [2, ArticleStatus::Published],
    [99, ArticleStatus::Published],
]);

it('exposes distinct colors per status', function (): void {
    $colors = array_map(fn (ArticleStatus $s) => $s->color(), ArticleStatus::cases());
    expect($colors)->toBe(['gray', 'warning', 'info', 'success']);
});
