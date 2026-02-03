<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\QueryableRepositoryInterface;

interface RoleRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Role;

    public function findForShow(Role $role): Role;

    public function createWithRelationships(array $data): Role;

    public function updateWithRelationships(Role $role, array $data): Role;

    public function findByName(string $name): ?Role;

    public function getSystemRoles(bool $is_system = false): Collection;
}
