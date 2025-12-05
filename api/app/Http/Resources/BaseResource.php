<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Configure case transformation for all resource keys.
     * Supported: 'snake', 'camel', 'none'
     */
    protected static string $case = 'none';

    /**
     * Child classes MUST implement this.
     * Should return raw array of data fields.
     */
    abstract protected function resolvePayload(Request $request): array;

    /**
     * Base toArray transformation applied to every resource.
     */
    final public function toArray(Request $request): array
    {
        // Child class implements these fields
        $payload = $this->resolvePayload($request);

        // Handle timestamps, case style, debugging
        $payload = $this->transformKeys($payload);

        return app()->environment('production')
            ? $payload
            : $this->withDebug($payload);
    }

    /**
     * Automatically convert timestamps to ISO8601.
     */
    protected function formatTimestamp($value): ?string
    {
        return $value?->toIso8601String();
    }

    /**
     * Transform array keys according to the chosen format.
     */
    protected function transformKeys(array $data): array
    {
        return match (static::$case) {
            'camel' => collect($data)->mapWithKeys(
                fn ($v, $k): array => [str($k)->camel()->value() => $v]
            )->toArray(),

            'snake' => collect($data)->mapWithKeys(
                fn ($v, $k): array => [str($k)->snake()->value() => $v]
            )->toArray(),

            default => $data,
        };
    }

    /**
     * Optionally inject debug details in non-production.
     */
    protected function withDebug(array $data): array
    {
        if (! app()->environment('production')) {
            $data['_debug'] = [
                'resource' => static::class,
                'loaded_relations' => array_keys($this->resource->getRelations()),
            ];
        }

        return $data;
    }
}
