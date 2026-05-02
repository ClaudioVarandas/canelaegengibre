<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\User;
use App\Services\Import\ContentSanitizer;
use App\Services\Import\TagMergeMap;
use Carbon\CarbonImmutable;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

#[Signature('canela:import {--images=} {--fresh} {--limit-articles=}')]
#[Description('Import the legacy Canela e Gengibre database (from the `legacy` connection) into the new schema.')]
class ImportLegacyCanela extends Command
{
    private const LEGACY_LOCALE = 'pt_PT';

    public function handle(ContentSanitizer $sanitizer, TagMergeMap $tagMerger): int
    {
        $imagesDir = rtrim((string) $this->option('images'), '/');
        if (blank($imagesDir) || ! is_dir($imagesDir)) {
            $this->error("--images path not provided or not a directory: '{$imagesDir}'");

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Truncating new-schema tables…');
            $this->truncateTargetTables();
        }

        $this->call('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);

        $this->info('Importing users…');
        $userMap = $this->importUsers();
        $this->line(' ✓ '.count($userMap).' users');

        $this->info('Importing categories…');
        $categoryMap = $this->importCategories();
        $this->line(' ✓ '.count($categoryMap).' categories');

        $this->info('Importing tags (with dedupe)…');
        $tagMap = $this->importTags($tagMerger);
        $this->line(' ✓ '.count($tagMap).' legacy tags collapsed into '.count(array_unique($tagMap)).' canonical tags');

        $this->info('Importing articles + translations + media…');
        $articleMap = $this->importArticles($userMap, $categoryMap, $sanitizer, $imagesDir);
        $this->line(' ✓ '.count($articleMap).' articles');

        $this->info('Importing article-tag pivot…');
        $pivot = $this->importPivot($articleMap, $tagMap);
        $this->line(" ✓ {$pivot} pivot rows");

        $this->newLine();
        $this->info('Import complete.');

        return self::SUCCESS;
    }

    private function truncateTargetTables(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'article_tag',
            'article_translations',
            'articles',
            'tag_translations',
            'tags',
            'category_translations',
            'categories',
            'model_has_roles',
            'media',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return array<int, int> legacy id => new id
     */
    private function importUsers(): array
    {
        $map = [];
        $legacyRoles = DB::connection('legacy')
            ->table('role_users')
            ->join('roles', 'role_users.role_id', '=', 'roles.id')
            ->select('role_users.user_id', 'roles.slug as role_slug')
            ->get()
            ->groupBy('user_id');

        $legacyUsers = DB::connection('legacy')->table('users')->whereNull('deleted_at')->get();

        foreach ($legacyUsers as $row) {
            $name = trim(trim((string) $row->first_name).' '.trim((string) $row->last_name));
            if (blank($name)) {
                $name = explode('@', (string) $row->email)[0] ?: "user-{$row->id}";
            }

            $now = now();
            $newId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $row->email,
                'email_verified_at' => ((int) $row->active === 1) ? $now : null,
                'bio' => $row->info,
                'password' => str_replace('\\$', '$', (string) $row->password),
                'created_at' => $this->parseTimestamp($row->created_at) ?? $now,
                'updated_at' => $this->parseTimestamp($row->updated_at) ?? $now,
            ]);
            $user = User::query()->findOrFail($newId);

            $roles = ($legacyRoles->get($row->id) ?? collect())->pluck('role_slug')->all();
            foreach ($roles as $slug) {
                if (in_array($slug, ['super-admin', 'author'], true)) {
                    $user->assignRole($slug);
                }
            }

            if (! $user->hasAnyRole(['super-admin', 'author'])) {
                $user->assignRole('author');
            }

            $map[(int) $row->id] = $user->id;
        }

        return $map;
    }

    /**
     * @return array<int, int>
     */
    private function importCategories(): array
    {
        $map = [];
        $rows = DB::connection('legacy')->table('categories')->orderBy('id')->get();

        foreach ($rows as $row) {
            if ($row->parent_id !== null) {
                continue;
            }
            $map[(int) $row->id] = $this->createCategory($row, null);
        }

        foreach ($rows as $row) {
            if ($row->parent_id === null) {
                continue;
            }
            $parentNewId = $map[(int) $row->parent_id] ?? null;
            $map[(int) $row->id] = $this->createCategory($row, $parentNewId);
        }

        return $map;
    }

    private function createCategory(object $row, ?int $parentNewId): int
    {
        $category = Category::query()->create([
            'parent_id' => $parentNewId,
            'sort' => (int) ($row->lft ?? 0),
            'created_at' => $this->parseTimestamp($row->created_at),
            'updated_at' => $this->parseTimestamp($row->updated_at),
        ]);

        CategoryTranslation::query()->create([
            'category_id' => $category->id,
            'locale' => self::LEGACY_LOCALE,
            'name' => $row->name,
            'slug' => $this->uniqueSlug(
                CategoryTranslation::class,
                (string) ($row->slug ?: Str::slug((string) $row->name)),
                self::LEGACY_LOCALE,
            ),
            'description' => null,
        ]);

        return $category->id;
    }

    /**
     * @return array<int, int> legacy tag id => new canonical tag id
     */
    private function importTags(TagMergeMap $merger): array
    {
        $map = [];
        $canonicalToNewId = [];

        $rows = DB::connection('legacy')
            ->table('tags')
            ->orderByDesc('count')
            ->get();

        foreach ($rows as $row) {
            $canonical = $merger->canonical((string) $row->slug);

            if (! isset($canonicalToNewId[$canonical])) {
                $tag = new Tag;
                $tag->save();
                TagTranslation::query()->create([
                    'tag_id' => $tag->id,
                    'locale' => self::LEGACY_LOCALE,
                    'name' => $row->name,
                    'slug' => $this->uniqueSlug(TagTranslation::class, $canonical, self::LEGACY_LOCALE),
                ]);
                $canonicalToNewId[$canonical] = $tag->id;
            }

            $map[(int) $row->id] = $canonicalToNewId[$canonical];
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $userMap
     * @param  array<int, int>  $categoryMap
     * @return array<int, int>
     */
    private function importArticles(array $userMap, array $categoryMap, ContentSanitizer $sanitizer, string $imagesDir): array
    {
        $map = [];
        $query = DB::connection('legacy')
            ->table('articles')
            ->whereNull('deleted_at')
            ->orderBy('id');

        if ($limit = (int) $this->option('limit-articles')) {
            $query->limit($limit);
        }

        $rows = $query->get();
        $progress = $this->output->createProgressBar($rows->count());
        $progress->start();

        foreach ($rows as $row) {
            $userId = $userMap[(int) $row->user_id] ?? null;
            $categoryId = $categoryMap[(int) $row->category_id] ?? null;
            if ($userId === null || $categoryId === null) {
                $progress->advance();

                continue;
            }

            $article = Article::query()->create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'status' => ArticleStatus::fromLegacyState((int) $row->state),
                'featured' => (bool) $row->featured,
                'views' => (int) ($row->views ?? 0),
                'published_at' => $this->parseTimestamp($row->published_at),
                'created_at' => $this->parseTimestamp($row->created_at),
                'updated_at' => $this->parseTimestamp($row->updated_at),
            ]);

            $excerpt = $sanitizer->sanitize((string) $row->excerpt);
            $content = $sanitizer->sanitize((string) $row->content);

            ArticleTranslation::query()->create([
                'article_id' => $article->id,
                'locale' => self::LEGACY_LOCALE,
                'title' => $row->title,
                'slug' => $this->uniqueSlug(ArticleTranslation::class, (string) $row->slug, self::LEGACY_LOCALE),
                'excerpt' => $excerpt,
                'content' => $content,
                'meta_title' => null,
                'meta_description' => null,
            ]);

            $this->attachCoverMedia($article, (string) $row->media_featured, $imagesDir);
            $this->attachContentMedia($article, $content, $imagesDir);

            $map[(int) $row->id] = $article->id;
            $progress->advance();
        }

        $progress->finish();
        $this->newLine();

        return $map;
    }

    /**
     * @param  array<int, int>  $articleMap
     * @param  array<int, int>  $tagMap
     */
    private function importPivot(array $articleMap, array $tagMap): int
    {
        $rows = DB::connection('legacy')
            ->table('tagged')
            ->where('taggable_type', 'App\\Models\\Article')
            ->get();

        $inserted = 0;
        foreach ($rows as $row) {
            $articleId = $articleMap[(int) $row->taggable_id] ?? null;
            $tagId = $tagMap[(int) $row->tag_id] ?? null;
            if ($articleId === null || $tagId === null) {
                continue;
            }
            $exists = DB::table('article_tag')
                ->where('article_id', $articleId)
                ->where('tag_id', $tagId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('article_tag')->insert([
                'article_id' => $articleId,
                'tag_id' => $tagId,
            ]);
            $inserted++;
        }

        return $inserted;
    }

    private function attachCoverMedia(Article $article, string $legacyPath, string $imagesDir): void
    {
        $source = $this->resolveLegacyImagePath($legacyPath, $imagesDir);
        if ($source === null) {
            return;
        }

        $article->addMedia($source)
            ->preservingOriginal()
            ->toMediaCollection('cover');
    }

    private function attachContentMedia(Article $article, string $html, string $imagesDir): void
    {
        if ($html === '') {
            return;
        }
        preg_match_all('/\/uploads\/media\/images\/[^\s"\'<>]+/i', $html, $matches);
        foreach (array_unique($matches[0]) as $path) {
            $source = $this->resolveLegacyImagePath($path, $imagesDir);
            if ($source === null) {
                continue;
            }
            $article->addMedia($source)
                ->preservingOriginal()
                ->withCustomProperties(['legacy_path' => $path])
                ->toMediaCollection('content');
        }
    }

    private function resolveLegacyImagePath(string $legacyPath, string $imagesDir): ?string
    {
        $legacyPath = trim($legacyPath);
        if ($legacyPath === '') {
            return null;
        }
        $relative = preg_replace('#^/?uploads/media/images/#', '', $legacyPath);
        $candidate = $imagesDir.'/'.ltrim((string) $relative, '/');

        return is_file($candidate) ? $candidate : null;
    }

    private function parseTimestamp(mixed $value): ?CarbonImmutable
    {
        if (blank($value) || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  class-string<Model>  $translationClass
     */
    private function uniqueSlug(string $translationClass, string $desired, string $locale): string
    {
        $desired = $desired !== '' ? $desired : 'item';
        $slug = $desired;
        $suffix = 2;

        while ($this->slugExists($translationClass, $slug, $locale)) {
            $slug = $desired.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $translationClass
     */
    private function slugExists(string $translationClass, string $slug, string $locale): bool
    {
        /** @var Builder $q */
        $q = $translationClass::query()->where('locale', $locale)->where('slug', $slug);

        return $q->exists();
    }
}
