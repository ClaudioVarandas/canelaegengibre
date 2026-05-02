<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Translation\TranslatorContract;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

#[Signature('canela:translate {--entity=articles : articles|categories|tags} {--locales=pt_BR,en} {--limit=} {--article=} {--dry-run}')]
#[Description('Translate pt_PT translations into pt_BR and/or en using the configured Claude-backed translator.')]
class TranslateArticles extends Command
{
    private const SOURCE_LOCALE = 'pt_PT';

    /**
     * @var array<string, array{model: class-string<Model>, fields: list<string>}>
     */
    private const ENTITY_SPECS = [
        'articles' => [
            'model' => Article::class,
            'fields' => ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description'],
        ],
        'categories' => [
            'model' => Category::class,
            'fields' => ['name', 'slug', 'description'],
        ],
        'tags' => [
            'model' => Tag::class,
            'fields' => ['name', 'slug'],
        ],
    ];

    public function handle(TranslatorContract $translator): int
    {
        $entity = (string) $this->option('entity');
        if (! isset(self::ENTITY_SPECS[$entity])) {
            $this->error("Unknown entity '{$entity}'. Supported: ".implode(', ', array_keys(self::ENTITY_SPECS)));

            return self::FAILURE;
        }
        $spec = self::ENTITY_SPECS[$entity];

        $targetLocales = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('locales')))));
        if ($targetLocales === []) {
            $this->error('No target locales given.');

            return self::FAILURE;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $spec['model'];
        $query = $modelClass::query()->with('translations');

        if ($articleId = $this->option('article')) {
            $query->whereKey((int) $articleId);
        }

        $query->whereHas('translations', fn ($q) => $q->where('locale', self::SOURCE_LOCALE));

        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $records = $query->get();
        if ($records->isEmpty()) {
            $this->warn('No records with a pt_PT translation found.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $translationModel = $modelClass::make()->translations()->getRelated()::class;
        $fields = $spec['fields'];

        $progress = $this->output->createProgressBar($records->count());
        $progress->start();

        $stats = ['translated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($records as $record) {
            $source = $this->extractSource($record, $fields);
            $existingLocales = $record->translations->pluck('locale')->all();
            $missing = array_values(array_diff($targetLocales, $existingLocales));

            if ($missing === []) {
                $stats['skipped']++;
                $progress->advance();

                continue;
            }

            try {
                $translated = $translator->translate(self::SOURCE_LOCALE, $source, $missing);
            } catch (Throwable $e) {
                $stats['failed']++;
                $this->newLine();
                $this->error("{$entity}#{$record->id}: {$e->getMessage()}");
                $progress->advance();

                continue;
            }

            if (! $dryRun) {
                try {
                    DB::transaction(function () use ($record, $translated, $translationModel): void {
                        foreach ($translated as $locale => $attrs) {
                            $attrs['slug'] = $this->uniqueSlug($translationModel, $attrs['slug'] ?? null, $attrs[array_key_first($attrs)] ?? '', $locale);
                            $record->translations()->updateOrCreate(
                                ['locale' => $locale],
                                $attrs,
                            );
                        }
                    });
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $this->newLine();
                    $this->error("{$entity}#{$record->id}: persist failed: {$e->getMessage()}");
                    $progress->advance();

                    continue;
                }
            }

            $stats['translated']++;
            $progress->advance();
        }

        $progress->finish();
        $this->newLine();
        $this->info("Translated: {$stats['translated']}, Skipped: {$stats['skipped']}, Failed: {$stats['failed']}");

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, ?string>
     */
    private function extractSource(Model $record, array $fields): array
    {
        $source = $record->translate(self::SOURCE_LOCALE);
        if ($source === null) {
            return array_fill_keys($fields, null);
        }

        $out = [];
        foreach ($fields as $field) {
            $out[$field] = $source->getAttribute($field);
        }

        return $out;
    }

    /**
     * @param  class-string<Model>  $translationModel
     */
    private function uniqueSlug(string $translationModel, ?string $desired, string $fallbackText, string $locale): string
    {
        $slug = (string) ($desired ?: Str::slug($fallbackText));
        if ($slug === '') {
            $slug = 'item';
        }
        $base = $slug;
        $suffix = 2;

        while ($translationModel::query()->where('locale', $locale)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
