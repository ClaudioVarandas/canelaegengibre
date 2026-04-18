<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Section::make('Profile')
                            ->columnSpan(['default' => 1, 'lg' => 2])
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn (?string $state) => filled($state))
                                    ->required(fn (string $operation) => $operation === 'create')
                                    ->minLength(8),
                                SpatieMediaLibraryFileUpload::make('avatar')
                                    ->collection('avatar')
                                    ->image()
                                    ->avatar(),
                                Textarea::make('bio')
                                    ->rows(4)
                                    ->maxLength(2000),
                            ]),

                        Section::make('Access')
                            ->columnSpan(1)
                            ->schema([
                                Select::make('roles')
                                    ->label('Roles')
                                    ->multiple()
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),
            ]);
    }
}
