<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function (): void {
    try {
        DB::connection('legacy')->table('articles')->count();
    } catch (Throwable $e) {
        $this->markTestSkipped('legacy DB unavailable: '.$e->getMessage());
    }
    Storage::fake('public');
});

it('fails when --images is missing', function (): void {
    $this->artisan('canela:import')->assertFailed();
});

it('imports a single legacy article with pt_PT translation + cover media', function (): void {
    $imagesDir = '/home/cvarandas/Downloads/cg/images/images';
    if (! is_dir($imagesDir)) {
        $this->markTestSkipped('legacy image folder missing');
    }

    $this->artisan('canela:import', [
        '--images' => $imagesDir,
        '--limit-articles' => 1,
    ])->assertSuccessful();

    expect(User::count())->toBeGreaterThanOrEqual(1);
    expect(Article::count())->toBe(1);
    expect(ArticleTranslation::where('locale', 'pt_PT')->count())->toBe(1);
    expect(Media::where('collection_name', 'cover')->count())->toBe(1);

    $author = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'author']))->first();
    expect($author)->not->toBeNull();
});
