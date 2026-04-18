<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Categories\Pages;

use App\Filament\Admin\Concerns\SyncsTranslations;
use App\Filament\Admin\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use SyncsTranslations;

    protected static string $resource = CategoryResource::class;

    /**
     * @return list<string>
     */
    protected function translationAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }
}
