<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface CompanyRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get paginated companies with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     *
     * @return LengthAwarePaginator<Company>
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator;

    /**
     * Find a company by ID with relationships loaded.
     */
    public function findById(int $companyId, ?int $teamId = null): Company;

    /**
     * Create a new company with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped company creation
     */
    public function createCompany(array $data, ?int $teamId = null): Company;

    /**
     * Update a company by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateCompany(Company $company, array $data, ?int $teamId = null): Company;

    /**
     * Delete a company by model instance.
     */
    public function deleteCompany(Company $company): bool;
}
