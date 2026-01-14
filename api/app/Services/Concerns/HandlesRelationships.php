<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

trait HandlesRelationships
{
    /**
     * Sync relationships for a model.
     */
    protected function syncRelationships(Model $model, array $data, array $relationshipKeys): void
    {
        foreach ($relationshipKeys as $key) {
            if (isset($data[$key])) {
                $model->{$key}()->sync($data[$key]);
            }
        }
    }

    /**
     * Attach relationships for a model.
     */
    protected function attachRelationships(Model $model, array $data, array $relationshipKeys): void
    {
        foreach ($relationshipKeys as $key) {
            if (isset($data[$key])) {
                $model->{$key}()->attach($data[$key]);
            }
        }
    }

    /**
     * Detach relationships for a model.
     */
    protected function detachRelationships(Model $model, array $relationshipKeys): void
    {
        foreach ($relationshipKeys as $key) {
            $model->{$key}()->detach();
        }
    }

    /**
     * Exclude relationship keys from data array.
     */
    protected function exceptRelationships(array $data, array $relationshipKeys): array
    {
        return Arr::except($data, $relationshipKeys);
    }
}
