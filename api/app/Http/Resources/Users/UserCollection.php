<?php

declare(strict_types=1);

namespace App\Http\Resources\Users;

use App\Http\Resources\BaseCollection;

final class UserCollection extends BaseCollection
{
    protected string $resourceClass = UserResource::class;
}
