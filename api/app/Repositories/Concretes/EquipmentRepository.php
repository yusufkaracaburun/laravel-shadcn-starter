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
    public function query(): QueryBuilder
    {
        $queryRequest = QueryBuilderRequest::fromRequest($this->request ?? request());

        return QueryBuilder::for($this->model(), $queryRequest)
            ->defaultSorts($this->getDefaultSorts())
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes());
    }

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

    protected function model(): string
    {
        return Equipment::class;
    }
}
