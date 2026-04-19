<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Models\Category;
use App\Models\Tag;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Translations')
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('locales')
                            ->persistTabInQueryString()
                            ->tabs(array_map(
                                fn (string $locale, array $meta) => Tab::make($meta['label'])
                                    ->schema(self::localeFields($locale)),
                                array_keys(config('canela.locales')),
                                config('canela.locales'),
                            )),
                    ]),

                Section::make('Metadata')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                    ->schema([
                        Select::make('status')
                            ->options(ArticleStatus::class)
                            ->default(ArticleStatus::Draft->value)
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->seconds(false),
                        Select::make('user_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->default(fn () => auth()->id())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn () => self::categoryOptions())
                            ->required()
                            ->searchable(),
                        Select::make('tags')
                            ->relationship('tags')
                            ->multiple()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 2, 'xl' => 2])
                            ->getOptionLabelFromRecordUsing(
                                fn (Tag $tag) => $tag->translate(app()->getLocale())?->name ?? "#{$tag->id}",
                            ),
                        Toggle::make('featured')
                            ->inline(false),
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                            ->image()
                            ->imageEditor()
                            ->label('Cover image')
                            ->columnSpan(['default' => 1, 'md' => 2, 'xl' => 2]),
                        FileUpload::make('og_image_path')
                            ->image()
                            ->directory('og-images')
                            ->disk('public')
                            ->label('OG image override'),
                    ]),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function localeFields(string $locale): array
    {
        $prefix = "translations.{$locale}.";
        $isDefault = $locale === config('canela.default_locale');

        return [
            TextInput::make($prefix.'title')
                ->required($isDefault)
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set) use ($prefix): void {
                    if (blank($state)) {
                        return;
                    }
                    $set($prefix.'slug', Str::slug($state));
                }),
            TextInput::make($prefix.'slug')
                ->requiredWith($prefix.'title')
                ->maxLength(255),
            Textarea::make($prefix.'excerpt')
                ->rows(3)
                ->maxLength(1000),
            Textarea::make($prefix.'content')
                ->rows(16)
                ->columnSpanFull(),
            TextInput::make($prefix.'meta_title')
                ->label('SEO title')
                ->maxLength(255),
            Textarea::make($prefix.'meta_description')
                ->label('SEO description')
                ->rows(2)
                ->maxLength(500),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function categoryOptions(): array
    {
        return Category::query()
            ->with('translations')
            ->get()
            ->mapWithKeys(fn (Category $c) => [
                $c->id => $c->translate(config('canela.default_locale'))?->name ?? "#{$c->id}",
            ])
            ->all();
    }
}
