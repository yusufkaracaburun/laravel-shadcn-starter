<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface QueryableRepositoryInterface extends BaseRepositoryInterface
{
    public function withRequest(Request $request): static;
    public function query(): QueryBuilder;
    public function getFiltered(array $columns = ['*']): Collection;
    public function paginateFiltered(Request $request, array $columns = ['*']): LengthAwarePaginator;
    public function getDefaultSorts(): array;
    public function getAllowedSorts(): array;
    public function getAllowedFields(): array;
    public function getAllowedIncludes(): array;
    public function getAllowedFilters(): array;
}
