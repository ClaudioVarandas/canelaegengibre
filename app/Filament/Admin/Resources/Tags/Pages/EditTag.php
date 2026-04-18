<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tags\Pages;

use App\Filament\Admin\Concerns\SyncsTranslations;
use App\Filament\Admin\Resources\Tags\TagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use SyncsTranslations;

    protected static string $resource = TagResource::class;

    /**
     * @return list<string>
     */
    protected function translationAttributes(): array
    {
        return ['name', 'slug'];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
