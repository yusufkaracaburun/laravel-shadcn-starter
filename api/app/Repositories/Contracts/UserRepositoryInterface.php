<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface UserRepositoryInterface
{
    public function getCurrentUser(User $user): User;
    public function getVerifiedUsers(): Collection;
    public function getActiveUsers(): Collection;
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): Collection;
}
