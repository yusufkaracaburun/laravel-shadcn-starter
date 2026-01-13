<?php

declare(strict_types=1);

namespace App\Http\Resources\Projects;

use App\Http\Resources\BaseCollection;

final class ProjectCollection extends BaseCollection
{
    protected string $resourceClass = ProjectResource::class;
}
