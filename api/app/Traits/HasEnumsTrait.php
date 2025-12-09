<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait providing helper methods for enums.
 *
 * This trait adds useful static and instance methods to enums for working
 * with labels, arrays, values, and translations.
 *
 * @example
 * PaymentStatus::labels();
 * // ['In behandeling', 'Betaald', 'Mislukt', 'Terugbetaald', 'Geannuleerd']
 *
 * PaymentStatus::toArray();
 * // All statuses in one dropdown array
 *
 * PaymentStatus::toArrayItem('paid');
 * // ['id' => 'paid', 'value' => 'paid', 'label' => 'Betaald', 'color' => 'success']
 *
 * PaymentStatus::values();
 * // ['pending', 'paid', 'failed', 'refunded', 'cancelled']
 */
trait HasEnumsTrait
{
    /**
     * Get all enum labels.
     *
     * @return array<string>
     */
    public static function labels(): array
    {
        return array_map(fn (self $case) => method_exists($case, 'label') ? $case->label() : $case->value, self::cases());
    }

    /**
     * Convert all enum cases to an array format suitable for dropdowns.
     *
     * @return array<int, array{id: string, value: string, label: string, color: string|null, style: string|null}>
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->map(fn (self $case): array => [
                'id' => $case->value,
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : $case->value,
                'color' => method_exists($case, 'color') ? $case->color() : null,
                'style' => method_exists($case, 'style') ? $case->style() : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Get all enum values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Convert a single enum case to an array format.
     *
     * @param  self|string  $value  The enum case or its string value
     * @return array{id: string, value: string, label: string, color: string|null, style: string|null}|null
     */
    public static function toArrayItem(self|string $value): ?array
    {
        $case = $value instanceof self ? $value : self::tryFrom($value);

        return $case
            ? [
                'id' => $case->value,
                'value' => $case->value,
                'label' => method_exists($case, 'label') ? $case->label() : $case->value,
                'color' => method_exists($case, 'color') ? $case->color() : null,
                'style' => method_exists($case, 'style') ? $case->style() : null,
            ]
            : null;
    }

    /**
     * Get the translated label for this enum case.
     */
    public function label(): string
    {
        $key = str($this->value)->snake()->lower()->toString();
        $translated = __($key);

        return $translated === $key
            ? ucfirst(str_replace('_', ' ', $this->value))
            : $translated;
    }
}
