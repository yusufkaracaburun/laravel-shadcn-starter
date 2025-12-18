<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Company;
use App\Services\BaseService;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanyCollection;
use App\Services\Contracts\CompanyServiceInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;

final class CompanyService extends BaseService implements CompanyServiceInterface
{
    private readonly CompanyRepositoryInterface $companyRepository;

    public function __construct(
        CompanyRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
        $this->companyRepository = $repository;
    }

    /**
     * Get paginated companies with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): CompanyCollection
    {
        $paginated = $this->companyRepository->getPaginated($perPage, $teamId);

        return new CompanyCollection($paginated);
    }

    /**
     * Find a company by ID with relationships loaded.
     */
    public function findById(int $companyId, ?int $teamId = null): CompanyResource
    {
        $company = $this->companyRepository->findById($companyId, $teamId);

        return new CompanyResource($company);
    }

    /**
     * Create a new company with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped company creation
     */
    public function createCompany(array $data, ?int $teamId = null): CompanyResource
    {
        $company = $this->companyRepository->createCompany($data, $teamId);

        return new CompanyResource($company);
    }

    /**
     * Update a company by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateCompany(Company $company, array $data, ?int $teamId = null): CompanyResource
    {
        $company = $this->companyRepository->updateCompany($company, $data, $teamId);

        return new CompanyResource($company);
    }

    /**
     * Delete a company by model instance.
     */
    public function deleteCompany(Company $company): bool
    {
        return $this->companyRepository->deleteCompany($company);
    }
}
