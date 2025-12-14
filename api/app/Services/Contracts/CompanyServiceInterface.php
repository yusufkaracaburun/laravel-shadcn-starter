<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Company;
use App\Services\BaseServiceInterface;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanyCollection;

interface CompanyServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated companies with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): CompanyCollection;

    /**
     * Find a company by ID with relationships loaded.
     */
    public function findById(int $companyId, ?int $teamId = null): CompanyResource;

    /**
     * Create a new company with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped company creation
     */
    public function createCompany(array $data, ?int $teamId = null): CompanyResource;

    /**
     * Update a company by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateCompany(Company $company, array $data, ?int $teamId = null): CompanyResource;

    /**
     * Delete a company by model instance.
     */
    public function deleteCompany(Company $company): bool;
}
