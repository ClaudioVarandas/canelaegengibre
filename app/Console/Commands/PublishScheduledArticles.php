<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('canela:publish-scheduled')]
#[Description('Promote scheduled articles whose publication time has arrived.')]
class PublishScheduledArticles extends Command
{
    public function handle(): int
    {
        $count = Article::query()
            ->where('status', ArticleStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->update(['status' => ArticleStatus::Published->value]);

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
