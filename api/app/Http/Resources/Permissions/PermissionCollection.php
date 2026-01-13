<?php

declare(strict_types=1);

namespace App\Http\Resources;

final class PermissionCollection extends BaseCollection
{
    protected string $resourceClass = PermissionResource::class;
}
