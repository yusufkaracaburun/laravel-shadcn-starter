<?php

declare(strict_types=1);

namespace App\Http\Resources\Activities;

use App\Http\Resources\BaseCollection;

final class ActivityCollection extends BaseCollection
{
    protected string $resourceClass = ActivityResource::class;
}
