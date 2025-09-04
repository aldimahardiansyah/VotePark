<?php

namespace App\Policies;

use App\Models\VotingSession;
use App\Models\User;

class VotingSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function view(User $user, VotingSession $votingSession): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $votingSession->event->site_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminSite();
    }

    public function update(User $user, VotingSession $votingSession): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $votingSession->event->site_id;
        }
        
        return false;
    }

    public function delete(User $user, VotingSession $votingSession): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $votingSession->event->site_id;
        }
        
        return false;
    }

    public function manage(User $user, VotingSession $votingSession): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        if ($user->isAdminSite()) {
            return $user->site_id === $votingSession->event->site_id;
        }
        
        return false;
    }
}