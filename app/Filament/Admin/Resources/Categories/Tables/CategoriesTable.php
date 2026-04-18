<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        $defaultLocale = config('canela.default_locale');

        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->state(fn ($record) => $record->translate($defaultLocale)?->name ?? '—')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'translations',
                        fn ($q) => $q->where('name', 'like', "%{$search}%"),
                    )),
                TextColumn::make('parent_name')
                    ->label('Parent')
                    ->state(fn ($record) => $record->parent?->translate($defaultLocale)?->name ?? '—'),
                TextColumn::make('articles_count')
                    ->label('Articles')
                    ->counts('articles')
                    ->sortable(),
                TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
