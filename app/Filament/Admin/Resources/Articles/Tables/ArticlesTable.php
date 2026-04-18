<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        $defaultLocale = config('canela.default_locale');

        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $user = auth()->user();
                if ($user instanceof User
                    && $user->hasRole('author')
                    && ! $user->hasRole('super-admin')) {
                    $query->where('articles.user_id', $user->id);
                }
            })
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('default_title')
                    ->label('Title')
                    ->state(fn ($record) => $record->translate($defaultLocale)?->title ?? '—')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'translations',
                        fn ($q) => $q->where('title', 'like', "%{$search}%"),
                    ))
                    ->wrap(),
                TextColumn::make('category.translations.name')
                    ->label('Category')
                    ->state(fn ($record) => $record->category?->translate($defaultLocale)?->name ?? '—')
                    ->toggleable(),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('featured')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ArticleStatus::class),
                TernaryFilter::make('featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
