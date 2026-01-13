<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Equipment;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Spatie\QueryBuilder\QueryBuilderRequest;
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
            'id',
            'name',
            'serial_number',
            'type',
            'status',
            'created_at',
            'updated_at',
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

    protected function model(): string
    {
        return Equipment::class;
    }
}
