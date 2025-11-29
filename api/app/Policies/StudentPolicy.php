<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Policy for Student model authorization.
 *
 * All permission checks are automatically team-scoped via TeamResolver,
 * which uses the user's current_team_id to resolve the team context.
 *
 * This is a placeholder - replace with actual Student model when available.
 */
final class StudentPolicy
{
    /**
     * Determine whether the user can view any students.
     * Permission check is team-scoped via TeamResolver.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('students.view');
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, mixed $student): bool
    {
        return $user->can('students.view');
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->can('students.create');
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, mixed $student): bool
    {
        return $user->can('students.update');
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, mixed $student): bool
    {
        return $user->can('students.delete');
    }
}
