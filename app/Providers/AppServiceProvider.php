<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Unit;
use App\Models\Event;
use App\Models\VotingSession;
use App\Policies\UnitPolicy;
use App\Policies\EventPolicy;
use App\Policies\VotingSessionPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        
        // Register policies
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(VotingSession::class, VotingSessionPolicy::class);
    }
}
