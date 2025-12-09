<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Services\BaseService;
use App\Services\Contracts\UserServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UserService extends BaseService implements UserServiceInterface
{
    public function __construct(
        UserRepositoryInterface $repository
    ) {
        $this->setRepository($repository);
    }
}
