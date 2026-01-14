<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Vehicle;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleRepository extends QueryableRepository implements VehicleRepositoryInterface
{
    protected function model(): string
    {
        return Vehicle::class;
    }

    public function getDefaultSorts(): array
    {
        return ['license_plate'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            ['make', 'model', 'year', 'license_plate']
        );
    }

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

    public function getAllowedIncludes(): array
    {
        return ['drivers'];
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('status'),
                AllowedFilter::partial('make'),
                AllowedFilter::partial('model'),
                AllowedFilter::partial('license_plate'),
                AllowedFilter::partial('vin'),
                AllowedFilter::exact('year'),
                AllowedFilter::scope('active'),
            ]
        );
    }
}
