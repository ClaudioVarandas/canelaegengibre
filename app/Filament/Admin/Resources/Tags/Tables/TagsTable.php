<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        $defaultLocale = config('canela.default_locale');

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->state(fn ($record) => $record->translate($defaultLocale)?->name ?? '—')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'translations',
                        fn ($q) => $q->where('name', 'like', "%{$search}%"),
                    )),
                TextColumn::make('articles_count')
                    ->label('Articles')
                    ->counts('articles')
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
