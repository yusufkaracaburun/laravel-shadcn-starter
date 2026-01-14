<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

abstract class BaseApiController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;

    /**
     * Get the authenticated user and refresh their data.
     */
    protected function getAuthenticatedUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        $user->refresh();

        return $user;
    }

    /**
     * Get the current team ID for the authenticated user.
     */
    protected function getCurrentTeamId(?User $user = null): ?int
    {
        $user ??= $this->getAuthenticatedUser();

        return $user->getAttributeValue('current_team_id');
    }

    /**
     * Respond with a resource.
     */
    protected function respondWithResource(mixed $resource): JsonResponse
    {
        return ApiResponse::success($resource);
    }

    /**
     * Respond with a collection.
     */
    protected function respondWithCollection(mixed $collection): JsonResponse
    {
        return ApiResponse::success($collection);
    }

    /**
     * Respond with a created resource.
     */
    protected function respondCreated(mixed $resource, ?string $message = null): JsonResponse
    {
        return ApiResponse::created($resource, [], $message ?? 'Resource created successfully');
    }

    /**
     * Respond with no content.
     */
    protected function respondNoContent(?string $message = null): JsonResponse
    {
        return ApiResponse::noContent($message);
    }

    /**
     * Respond with prerequisites data.
     */
    protected function respondWithPrerequisites(array $data): JsonResponse
    {
        return ApiResponse::success($data);
    }

    /**
     * Check if user is a super admin.
     */
    protected function isSuperAdmin(User $user): bool
    {
        // Use the exact same logic as BasePolicy::before() to ensure consistency
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

        // Clear permission cache and roles relation to ensure fresh check
        $permissionRegistrar->forgetCachedPermissions();

        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $user->unsetRelation('roles');
        $isSuperAdmin = $user->hasRole('super-admin');

        // Restore original team context
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);

        return $isSuperAdmin;
    }
}
