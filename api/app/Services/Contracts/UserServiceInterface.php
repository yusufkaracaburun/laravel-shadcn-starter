<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface UserServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): UserCollection;

    public function getAll(array $columns = ['*']): UserCollection;

    public function findById(int $id): UserResource;

    public function create(array $data): UserResource;

    public function update(Model $model, array $data): UserResource;

    public function delete(Model $model): bool;

    public function getCurrentUser(User $user): UserResource;

    public function getVerifiedUsers(): AnonymousResourceCollection;

    public function getActiveUsers(): AnonymousResourceCollection;

    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): AnonymousResourceCollection;

    public function getAllFiltered(): AnonymousResourceCollection;

    public function getVerifiedFiltered(): AnonymousResourceCollection;
}
