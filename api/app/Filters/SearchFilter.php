<?php

declare(strict_types=1);

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final readonly class SearchFilter implements Filter
{
    /**
     * @param  array<int, string>  $fields
     *                                      Example: ['invoice_number', 'notes', 'customer.name', 'customer.email']
     */
    public function __construct(private array $fields) {}

    /**
     * Apply the filter logic.
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        $searchValue = mb_trim((string) $value);

        if ($searchValue === '') {
            return;
        }

        $query->where(function (Builder $query) use ($searchValue): void {
            foreach ($this->fields as $field) {
                // Detect relation fields (e.g. "customer.name")
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);

                    $query->orWhereHas($relation, function (Builder $relationQuery) use ($column, $searchValue): void {
                        $relationQuery->where($column, 'like', '%' . $searchValue . '%');
                    });
                } else {
                    // Normal column on the model
                    $query->orWhere($field, 'like', '%' . $searchValue . '%');
                }
            }
        });
    }
}
