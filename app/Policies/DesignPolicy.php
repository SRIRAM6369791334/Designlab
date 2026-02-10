<?php

namespace App\Policies;

use App\Models\Design;
use App\Models\User;

class DesignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Design $design): bool
    {
        return $user->isAdmin() || $design->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, Design $design): bool
    {
        return $user->isAdmin() || $design->user_id === $user->id;
    }

    public function delete(User $user, Design $design): bool
    {
        return $this->update($user, $design);
    }
}
