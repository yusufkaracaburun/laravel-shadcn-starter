<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QueryableRepositoryInterface
{
    public function withRequest(Request $request): static;
    public function query(): QueryBuilder;
    public function getDefaultSorts(): array;
    public function getAllowedSorts(): array;
    public function getAllowedFields(): array;
    public function getAllowedIncludes(): array;
    public function getAllowedFilters(): array;
}
