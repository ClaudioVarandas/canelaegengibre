<?php

declare(strict_types=1);

use Anthropic\Client;
use App\Services\Translation\ClaudeTranslator;

function callPrivate(ClaudeTranslator $t, string $method, array $args): mixed
{
    $ref = new ReflectionMethod($t, $method);
    $ref->setAccessible(true);

    return $ref->invokeArgs($t, $args);
}

function makeTranslator(): ClaudeTranslator
{
    return new ClaudeTranslator(client: new Client(apiKey: 'test'), model: 'test');
}

it('parses a clean json response', function (): void {
    $translator = makeTranslator();
    $raw = json_encode([
        'pt_BR' => ['title' => 'Bolo', 'slug' => 'bolo'],
        'en' => ['title' => 'Cake', 'slug' => 'cake'],
    ]);

    $out = callPrivate($translator, 'parseResponse', [$raw, ['pt_BR', 'en'], ['title', 'slug']]);

    expect($out)->toBe([
        'pt_BR' => ['title' => 'Bolo', 'slug' => 'bolo'],
        'en' => ['title' => 'Cake', 'slug' => 'cake'],
    ]);
});

it('strips ```json fences before parsing', function (): void {
    $translator = makeTranslator();
    $raw = "```json\n".json_encode(['pt_BR' => ['title' => 'Bolo']])."\n```";

    $out = callPrivate($translator, 'parseResponse', [$raw, ['pt_BR'], ['title']]);

    expect($out)->toBe(['pt_BR' => ['title' => 'Bolo']]);
});

it('throws when response misses a target locale', function (): void {
    $translator = makeTranslator();
    $raw = json_encode(['pt_BR' => ['title' => 'Bolo']]);

    expect(fn () => callPrivate($translator, 'parseResponse', [$raw, ['pt_BR', 'en'], ['title']]))
        ->toThrow(RuntimeException::class, "missing locale 'en'");
});

it('throws when response is unparseable', function (): void {
    $translator = makeTranslator();

    expect(fn () => callPrivate($translator, 'parseResponse', ['not json at all', ['pt_BR'], ['title']]))
        ->toThrow(RuntimeException::class, 'parseable JSON');
});

it('coerces non-string field values to empty strings', function (): void {
    $translator = makeTranslator();
    $raw = json_encode(['pt_BR' => ['title' => 'Bolo', 'count' => 3]]);

    $out = callPrivate($translator, 'parseResponse', [$raw, ['pt_BR'], ['title', 'count']]);

    expect($out['pt_BR']['count'])->toBe('');
});
