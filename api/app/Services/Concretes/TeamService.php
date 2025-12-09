<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Services\BaseService;
use App\Services\Contracts\TeamServiceInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamService extends BaseService implements TeamServiceInterface
{
    public function __construct(
        TeamRepositoryInterface $repository
    ) {
        $this->setRepository($repository);
    }
}
