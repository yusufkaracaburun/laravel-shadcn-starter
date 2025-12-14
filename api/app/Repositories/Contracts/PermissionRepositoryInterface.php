<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Repositories\QueryableRepositoryInterface;

interface PermissionRepositoryInterface extends QueryableRepositoryInterface
{
    public function getPermissions(): Collection;
}
