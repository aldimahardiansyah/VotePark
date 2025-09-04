<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $event->site_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $event->site_id;
        }
        
        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $event->site_id;
        }
        
        return false;
    }

    public function manageVoting(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $event->site_id;
        }
        
        return false;
    }
}