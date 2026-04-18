<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tags\Pages;

use App\Filament\Admin\Concerns\SyncsTranslations;
use App\Filament\Admin\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
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
}
