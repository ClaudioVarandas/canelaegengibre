<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Models\Article;

it('promotes scheduled articles whose publish date has passed', function (): void {
    $due = Article::factory()->create([
        'status' => ArticleStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]);
    $future = Article::factory()->create([
        'status' => ArticleStatus::Scheduled,
        'published_at' => now()->addHour(),
    ]);
    $draft = Article::factory()->create([
        'status' => ArticleStatus::Draft,
        'published_at' => now()->subDay(),
    ]);

    $this->artisan('canela:publish-scheduled')
        ->assertSuccessful()
        ->expectsOutputToContain('1');

    expect($due->fresh()->status)->toBe(ArticleStatus::Published);
    expect($future->fresh()->status)->toBe(ArticleStatus::Scheduled);
    expect($draft->fresh()->status)->toBe(ArticleStatus::Draft);
});

it('reports zero when there is nothing to publish', function (): void {
    $this->artisan('canela:publish-scheduled')
        ->assertSuccessful()
        ->expectsOutputToContain('0');
});
