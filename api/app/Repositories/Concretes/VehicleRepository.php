<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Vehicle;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleRepository extends QueryableRepository implements VehicleRepositoryInterface
{
    /**
     * Allowed filters.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::partial('make'),
            AllowedFilter::partial('model'),
            AllowedFilter::partial('license_plate'),
            AllowedFilter::partial('vin'),
            AllowedFilter::exact('year'),
        ];
    }

    /**
     * Allowed sorts.
     */
    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'make', '-make',
            'model', '-model',
            'year', '-year',
            'license_plate', '-license_plate',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    /**
     * Allowed fields.
     */
    public function getAllowedFields(): array
    {
        return [
            'id',
            'make',
            'model',
            'year',
            'license_plate',
            'vin',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Allowed includes.
     */
    public function getAllowedIncludes(): array
    {
        return ['drivers'];
    }

    /**
     * Find a vehicle.
     */
    public function findOrFail(int $id, array $columns = ['*']): Vehicle
    {
        return Vehicle::query()->findOrFail($id, $columns);
    }

    protected function model(): string
    {
        return Vehicle::class;
    }
}
