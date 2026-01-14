<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Equipment;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use App\Repositories\Contracts\EquipmentRepositoryInterface;

final class EquipmentRepository extends QueryableRepository implements EquipmentRepositoryInterface
{
    protected function model(): string
    {
        return Equipment::class;
    }

    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            ['name', 'serial_number', 'type', 'status']
        );
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
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::partial('name'),
                AllowedFilter::partial('serial_number'),
                AllowedFilter::partial('type'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('active'),
            ]
        );
    }
}
