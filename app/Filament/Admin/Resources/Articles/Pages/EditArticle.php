<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Pages;

use App\Filament\Admin\Concerns\SyncsTranslations;
use App\Filament\Admin\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    use SyncsTranslations;

    protected static string $resource = ArticleResource::class;

    /**
     * @return list<string>
     */
    protected function translationAttributes(): array
    {
        return ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description'];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
