<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CompanyIndexRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Helpers\Cache\CacheInvalidationService;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Services\Contracts\CompanyServiceInterface;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class CompanyController extends Controller
{
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly CompanyServiceInterface $companyService
    ) {}

    /**
     * Display a paginated list of companies.
     *
     * @authenticated
     */
    public function index(CompanyIndexRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $user->refresh();
        $teamId = $user->getAttributeValue('current_team_id');

        $perPage = (int) ($validated['per_page'] ?? 10);
        $collection = $this->companyService->getPaginated($perPage, $teamId);

        return ApiResponse::success($collection);
    }

    /**
     * Get a specific company by ID.
     *
     * @authenticated
     */
    public function show(Company $company): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $companyResource = $this->companyService->findById($company->id, $teamId);

        return ApiResponse::success($companyResource);
    }

    /**
     * Create a new company.
     *
     * @authenticated
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        $companyResource = $this->companyService->createCompany($request->validated(), $teamId);

        return ApiResponse::created($companyResource);
    }

    /**
     * Update company information.
     *
     * @authenticated
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        // Check if company belongs to user's team (this will throw 404 if not found)
        $this->companyService->findById($company->id, $teamId);

        $validated = $request->validated();

        $companyResource = $this->companyService->updateCompany($company, $validated, $teamId);

        // Invalidate company and team caches
        CacheInvalidationService::invalidateTeam($teamId ?? 0);

        return ApiResponse::success($companyResource);
    }

    /**
     * Delete a company.
     *
     * @authenticated
     */
    public function destroy(Company $company): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->refresh();

        $teamId = $currentUser->getAttributeValue('current_team_id');

        // Check if company belongs to user's team
        $companyResource = $this->companyService->findById($company->id, $teamId);

        $this->companyService->deleteCompany($companyResource->resource);

        // Invalidate company and team caches
        if ($teamId) {
            CacheInvalidationService::invalidateTeam($teamId);
        }

        return ApiResponse::noContent('Company deleted successfully');
    }
}
