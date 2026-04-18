<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Redirect $redirect): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return false;
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return false;
    }
}
