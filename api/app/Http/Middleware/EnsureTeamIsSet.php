<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class EnsureTeamIsSet
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has a current_team_id set and
     * automatically sets the team context for Spatie permissions.
     *
     * @param  Closure(Request): (SymfonyResponse)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Super admin bypasses team requirement
        // Check hasRole with team context cleared and cache cleared
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null);

        // Clear cache and roles relation for fresh check
        $permissionRegistrar->forgetCachedPermissions();

        $user->unsetRelation('roles');
        $isSuperAdmin = $user->hasRole('super-admin');

        $permissionRegistrar->setPermissionsTeamId($originalTeamId);

        if ($isSuperAdmin) {
            return $next($request);
        }

        if (! $user->current_team_id) {
            return response()->json([
                'message' => 'No active team selected. Please select a team first.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Set the team context for Spatie permissions
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $permissionRegistrar->setPermissionsTeamId($user->current_team_id);

        $response = $next($request);

        // Reset team context after request
        $permissionRegistrar->setPermissionsTeamId(null);

        return $response;
    }
}
