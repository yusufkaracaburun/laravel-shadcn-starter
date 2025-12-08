<?php

declare(strict_types=1);

namespace App\Traits;

use Cknow\Money\Money;
use App\Support\MoneyHelper;

/**
 * Trait for models that have money fields.
 *
 * Provides convenient methods for working with money attributes.
 * Follows DRY principle and Single Responsibility Principle.
 */
trait HasMoney
{
    /**
     * Get a Money instance from a cents value stored in the database.
     *
     * @param  int|null  $cents  Amount in cents
     * @param  string|null  $currency  Currency code (defaults to EUR)
     */
    protected function moneyFromCents(?int $cents, ?string $currency = null): ?Money
    {
        if ($cents === null) {
            return null;
        }

        return MoneyHelper::fromCents($cents, $currency);
    }

    /**
     * Get a Money instance from a decimal value.
     *
     * @param  float|string|null  $decimal  Amount in decimal
     * @param  string|null  $currency  Currency code (defaults to EUR)
     */
    protected function moneyFromDecimal(float|string|null $decimal, ?string $currency = null): ?Money
    {
        if ($decimal === null) {
            return null;
        }

        return MoneyHelper::fromDecimal($decimal, $currency);
    }

    /**
     * Format a money value for display.
     */
    protected function formatMoney(?Money $money): ?string
    {
        return $money?->format();
    }
}
