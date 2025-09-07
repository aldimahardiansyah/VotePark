<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function view(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $unit->site_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function update(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $unit->site_id;
        }
        
        return false;
    }

    public function delete(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $unit->site_id;
        }
        
        return false;
    }
}