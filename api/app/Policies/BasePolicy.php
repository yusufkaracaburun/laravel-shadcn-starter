<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Base policy class providing common authorization logic.
 *
 * All policies extending this class will automatically have super-admin bypass
 * logic that checks for the 'super-admin' role with team context cleared.
 */
abstract class BasePolicy
{
    /**
     * Perform pre-authorization checks on the model.
     * Super admin bypasses all authorization checks.
     */
    final public function before(User $user): ?bool
    {
        // Check for super-admin with team context cleared (global role)
        $permissionRegistrar = resolve(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

        // Clear permission cache and roles relation to ensure fresh check
        $permissionRegistrar->forgetCachedPermissions();

        if (! $user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $user->unsetRelation('roles');
        $isSuperAdmin = $user->hasRole('super-admin');

        // Restore original team context
        $permissionRegistrar->setPermissionsTeamId($originalTeamId);

        if ($isSuperAdmin) {
            return true;
        }

        return null;
    }
}
