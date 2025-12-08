<?php

declare(strict_types=1);

namespace App\Casts;

use Cknow\Money\Money;
use App\Support\MoneyHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Money Cast for Eloquent Models
 *
 * Automatically converts between database integer (cents) and Money objects.
 * Follows Single Responsibility Principle.
 */
final class MoneyCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return MoneyHelper::fromCents((int) $value);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return MoneyHelper::toCents($value);
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) || is_string($value)) {
            $money = MoneyHelper::fromDecimal($value);

            return MoneyHelper::toCents($money);
        }

        return null;
    }
}
