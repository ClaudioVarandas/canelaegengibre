<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Redirect')
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextInput::make('from_path')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Legacy request path, must start with "/"')
                            ->columnSpan(['default' => 1, 'md' => 2]),
                        Select::make('code')
                            ->options([
                                301 => '301 Permanent',
                                302 => '302 Found',
                                307 => '307 Temporary Redirect',
                                308 => '308 Permanent Redirect',
                            ])
                            ->default(301)
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        TextInput::make('to_path')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Destination path or absolute URL')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
