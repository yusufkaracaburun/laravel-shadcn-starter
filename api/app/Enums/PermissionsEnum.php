<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * Permissions enumeration.
 *
 * Represents the various permission actions available in the system.
 * Note: This enum may be used for UI purposes or legacy code.
 * The application primarily uses Spatie Permission package for permission management.
 */
enum PermissionsEnum: string
{
    use HasEnumsTrait;

    case VIEW_ANY = 'view any';
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    /**
     * Get all permission values for a specific feature.
     *
     * @param  string  $feature  The feature name (e.g., 'users', 'invoices')
     * @return array<string>
     */
    public static function values(string $feature): array
    {
        return collect(self::cases())
            ->map(fn (self $case): string => $case->value.' '.$feature)
            ->values()
            ->all();
    }
}
