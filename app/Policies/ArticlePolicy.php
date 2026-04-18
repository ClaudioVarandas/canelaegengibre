<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'author']);
    }

    public function view(User $user, Article $article): bool
    {
        return $user->hasAnyRole(['super-admin', 'author']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'author']);
    }

    public function update(User $user, Article $article): bool
    {
        return $article->user_id === $user->id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $article->user_id === $user->id;
    }

    public function restore(User $user, Article $article): bool
    {
        return $article->user_id === $user->id;
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return false;
    }
}
