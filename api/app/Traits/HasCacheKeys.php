<?php

declare(strict_types=1);

namespace App\Traits;

trait HasCacheKeys
{
    /**
     * Get cache keys for model endpoints.
     *
     * Models can override this by defining their own CACHE constant.
     * If not overridden, cache keys are generated based on the model's table name.
     *
     * @return array<string, string>
     */
    public static function getCacheKeys(): array
    {
        // Check if model has defined its own CACHE constant
        $cacheConstant = constant(static::class . '::CACHE');
        if ($cacheConstant !== false && is_array($cacheConstant)) {
            /** @var array<string, string> $cacheConstant */
            return $cacheConstant;
        }

        // Generate cache keys based on table name
        $table = (new static)->getTable();
        $resource = str_replace('_', '.', $table);

        return [
            'index' => "api.{$resource}.index",
            'show'  => "api.{$resource}.show",
        ];
    }
}
