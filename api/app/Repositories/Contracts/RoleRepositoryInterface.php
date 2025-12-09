<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\QueryableRepositoryInterface;

interface RoleRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get all roles.
     */
    public function getRoles(): Collection;

    /**
     * Get role by name.
     */
    public function findByName(string $name): ?Role;

    /**
     * Get non-system roles.
     */
    public function getNonSystemRoles(): Collection;
}
