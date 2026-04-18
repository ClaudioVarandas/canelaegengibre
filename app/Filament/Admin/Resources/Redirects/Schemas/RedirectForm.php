<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('from_path')
                    ->required()
                    ->prefix('https://…')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Legacy request path, must start with "/"'),
                TextInput::make('to_path')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Destination path or absolute URL'),
                Select::make('code')
                    ->options([
                        301 => '301 Permanent',
                        302 => '302 Found',
                        307 => '307 Temporary Redirect',
                        308 => '308 Permanent Redirect',
                    ])
                    ->default(301)
                    ->required(),
            ]);
    }
}
