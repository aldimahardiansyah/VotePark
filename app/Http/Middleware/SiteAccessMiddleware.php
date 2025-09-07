<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Site;
use Symfony\Component\HttpFoundation\Response;

class SiteAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Superadmin can access everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // For admin_site and tenant, check if they have site access
        if ($user->site_id === null) {
            abort(403, 'You are not assigned to any site.');
        }

        // Check if user is trying to access resources from their site
        $siteId = $request->route('site') ?? $user->site_id;
        
        if ($user->site_id != $siteId) {
            abort(403, 'You can only access resources from your assigned site.');
        }

        return $next($request);
    }
}
