<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function label(): string
    {
        return __('article.status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Pending => 'warning',
            self::Scheduled => 'info',
            self::Published => 'success',
        };
    }

    public static function fromLegacyState(int $state): self
    {
        // Legacy Lavalite convention: 1 and 2 are both visible/published states;
        // 0 and -1 are unpublished/draft/trashed-but-kept.
        return match (true) {
            $state >= 1 => self::Published,
            default => self::Draft,
        };
    }
}
