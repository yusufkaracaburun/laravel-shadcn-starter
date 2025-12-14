<?php

declare(strict_types=1);

namespace App\Helpers;

use Cknow\Money\Money;

/**
 * Money Helper Service
 *
 * Provides a facade-like interface for money operations.
 * Follows Facade pattern and Single Responsibility Principle.
 * Configured for Netherlands (EUR, nl_NL).
 */
final class MoneyHelper
{
    /**
     * Create a Money instance from integer amount (in cents).
     *
     * @param  int  $amount  Amount in cents (e.g., 1000 = €10.00)
     * @param  string|null  $currency  Currency code (defaults to EUR)
     */
    public static function fromCents(int $amount, ?string $currency = null): Money
    {
        $currency ??= config('money.defaultCurrency', 'EUR');

        return Money::{$currency}($amount);
    }

    /**
     * Create a Money instance from decimal amount.
     *
     * @param  float|string  $amount  Amount in decimal (e.g., 10.50 = €10.50)
     * @param  string|null  $currency  Currency code (defaults to EUR)
     */
    public static function fromDecimal(float|string $amount, ?string $currency = null): Money
    {
        $currency ??= config('money.defaultCurrency', 'EUR');

        // Convert decimal to cents
        $cents = (int) round((float) $amount * 100);

        return Money::{$currency}($cents);
    }

    /**
     * Format money for display.
     */
    public static function format(Money $money): string
    {
        return $money->format();
    }

    /**
     * Parse a money string to Money object.
     *
     * @param  string  $value  Money string (e.g., "€10,50" or "10.50")
     */
    public static function parse(string $value): Money
    {
        return Money::parse($value);
    }

    /**
     * Get the amount in cents from a Money object.
     */
    public static function toCents(Money $money): int
    {
        return (int) $money->getAmount();
    }

    /**
     * Get the amount as decimal from a Money object.
     */
    public static function toDecimal(Money $money): float
    {
        return (float) $money->getAmount() / 100;
    }
}
