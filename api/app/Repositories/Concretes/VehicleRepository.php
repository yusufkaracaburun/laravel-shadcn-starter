<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleRepository extends QueryableRepository implements VehicleRepositoryInterface
{

    public function getDefaultSorts(): array
    {
        return ['license_plate'];
    }

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
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::partial('make'),
            AllowedFilter::partial('model'),
            AllowedFilter::partial('license_plate'),
            AllowedFilter::partial('vin'),
            AllowedFilter::exact('year'),
            AllowedFilter::scope('active'),
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): Vehicle
    {
        return Vehicle::query()
            ->with(['drivers'])
            ->findOrFail($id, $columns);
    }

    /**
     * Find vehicle for show endpoint with relationships loaded.
     */
    public function findForShow(Vehicle $vehicle): Vehicle
    {
        return $this->loadRelationships($vehicle);
    }

    /**
     * Create a new vehicle and load relationships.
     */
    public function createWithRelationships(array $data): Vehicle
    {
        /** @var Vehicle $vehicle */
        $vehicle = parent::create($data);

        return $this->loadRelationships($vehicle);
    }

    /**
     * Update vehicle and load relationships.
     */
    public function updateWithRelationships(Vehicle $vehicle, array $data): Vehicle
    {
        /** @var Vehicle $updated */
        $updated = parent::update($vehicle, $data);

        return $this->loadRelationships($updated);
    }

    /**
     * Standardize relationship loading in one place.
     */
    private function loadRelationships(Model $vehicle): Vehicle
    {
        /** @var Vehicle $vehicle */
        return $vehicle->load('drivers');
    }

    protected function model(): string
    {
        return Vehicle::class;
    }
}
