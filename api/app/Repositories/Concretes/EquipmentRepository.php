<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Equipment;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\EquipmentRepositoryInterface;

final class EquipmentRepository extends QueryableRepository implements EquipmentRepositoryInterface
{
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id', '-id',
            'name', '-name',
            'serial_number', '-serial_number',
            'type', '-type',
            'status', '-status',
            'created_at', '-created_at',
            'updated_at', '-updated_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'serial_number',
            'type',
            'status',
            'image',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return [];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('name'),
            AllowedFilter::partial('serial_number'),
            AllowedFilter::partial('type'),
            AllowedFilter::exact('status'),
            AllowedFilter::scope('active'),
            AllowedFilter::scope('created_at'),
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): Equipment
    {
        return Equipment::query()->findOrFail($id, $columns);
    }

    /**
     * Find equipment for show endpoint with relationships loaded.
     */
    public function findForShow(Equipment $equipment): Equipment
    {
        return $this->loadRelationships($equipment);
    }

    /**
     * Create a new equipment and load relationships.
     */
    public function createWithRelationships(array $data): Equipment
    {
        /** @var Equipment $equipment */
        $equipment = parent::create($data);

        return $this->loadRelationships($equipment);
    }

    /**
     * Update equipment and load relationships.
     */
    public function updateWithRelationships(Equipment $equipment, array $data): Equipment
    {
        /** @var Equipment $updated */
        $updated = parent::update($equipment, $data);

        return $this->loadRelationships($updated);
    }

    protected function model(): string
    {
        return Equipment::class;
    }

    /**
     * Standardize relationship loading in one place.
     */
    private function loadRelationships(Model $equipment): Equipment
    {
        /** @var Equipment $equipment */
        return $equipment;
    }
}
