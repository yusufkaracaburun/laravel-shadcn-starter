<?php

declare(strict_types=1);

namespace App\Http\Resources;

final class ProjectCollection extends BaseCollection
{
    protected string $resourceClass = ProjectResource::class;
}
