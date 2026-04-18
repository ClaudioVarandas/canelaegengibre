<?php

declare(strict_types=1);

use App\Services\Import\TagMergeMap;

it('merges known legacy slug duplicates to a canonical slug', function (string $legacy, string $canonical): void {
    $merger = new TagMergeMap;

    expect($merger->canonical($legacy))->toBe($canonical);
})->with([
    ['receita', 'receitas'],
    ['receitasrapidas', 'receitas'],
    ['panqueca', 'panquecas'],
    ['semaçúcar', 'sem-acucar'],
    ['semacucar', 'sem-acucar'],
    ['organização', 'organizacao'],
    ['postreino', 'pos-treino'],
    ['pretreino', 'pre-treino'],
]);

it('normalizes unmapped slugs via Str::slug', function (): void {
    $merger = new TagMergeMap;

    expect($merger->canonical('Pão Caseiro'))->toBe('pao-caseiro');
});

it('preserves already-canonical unmapped slugs', function (): void {
    $merger = new TagMergeMap;

    expect($merger->canonical('chocolate'))->toBe('chocolate');
});
