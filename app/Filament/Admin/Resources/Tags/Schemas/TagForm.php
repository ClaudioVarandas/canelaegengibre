<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
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
        ];
    }
}
