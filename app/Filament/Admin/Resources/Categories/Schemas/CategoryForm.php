<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Section::make('Translations')
                            ->columnSpan(['default' => 1, 'lg' => 2])
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

                        Section::make('Hierarchy')
                            ->columnSpan(1)
                            ->schema([
                                Select::make('parent_id')
                                    ->label('Parent')
                                    ->options(fn (?Category $record) => Category::query()
                                        ->when($record, fn ($q) => $q->whereKeyNot($record->id))
                                        ->whereNull('parent_id')
                                        ->get()
                                        ->mapWithKeys(fn (Category $c) => [
                                            $c->id => $c->translate(config('canela.default_locale'))?->name ?? "#{$c->id}",
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->nullable(),
                                TextInput::make('sort')
                                    ->numeric()
                                    ->default(0),
                            ]),
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
            TextInput::make($prefix.'name')
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
                ->requiredWith($prefix.'name')
                ->maxLength(255),
            Textarea::make($prefix.'description')
                ->rows(3)
                ->maxLength(1000),
        ];
    }
}
