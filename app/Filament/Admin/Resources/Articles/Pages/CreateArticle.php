<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Articles\Pages;

use App\Filament\Admin\Concerns\SyncsTranslations;
use App\Filament\Admin\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
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
}
