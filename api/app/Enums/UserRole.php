<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * User role enumeration.
 *
 * Represents the various roles a user can have within the system.
 * Note: This enum may be used for UI purposes or legacy code.
 * The application primarily uses Spatie Permission package for role management.
 */
enum UserRole: string
{
    use HasEnumsTrait;

    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case USER = 'user';
    case CUSTOMER = 'customer';

    /**
     * Get all permissions for all features.
     *
     * @return array<string>
     */
    public static function permissions(): array
    {
        $features = ['companies', 'invoices', 'items', 'permissions', 'roles', 'users'];

        return collect($features)->map(fn (string $feature): array => PermissionsEnum::values($feature))->values()->all();
    }

    /**
     * Get permissions for this specific role.
     *
     * @return array<string>
     */
    public function rolePermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => $this->permissions(),
            self::MANAGER => ['view users', 'view companies', 'view invoices', 'update invoices', 'view items'],
            self::USER, self::CUSTOMER => ['view invoices'],
        };
    }

    /**
     * Get the color associated with this role.
     */
    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'danger',
            self::ADMIN => 'warning',
            self::MANAGER => 'info',
            self::USER => 'secondary',
            self::CUSTOMER => 'primary',
        };
    }

    /**
     * Get the CSS style class associated with this role.
     */
    public function style(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'badge badge-light-danger',
            self::ADMIN => 'badge badge-light-warning',
            self::MANAGER => 'badge badge-light-info',
            self::USER => 'badge badge-light-secondary',
            self::CUSTOMER => 'badge badge-light-primary',
        };
    }

    /**
     * Check if the role is admin (super admin or admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN], true);
    }

    /**
     * Check if the role is manager.
     */
    public function isManager(): bool
    {
        return $this === self::MANAGER;
    }

    /**
     * Check if the role is customer.
     */
    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    /**
     * Check if the role is user.
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }
}
