<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\QueryableRepositoryInterface;

interface RoleRepositoryInterface extends QueryableRepositoryInterface
{
    public function getSystemRoles(bool $is_system = false): Collection;
}
