<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\HasEnumsTrait;

/**
 * User company role enumeration.
 *
 * Represents the various roles a user can have within a company/team context.
 * This enum is used for team-specific roles (admin, editor, viewer).
 */
enum UserCompanyRole: string
{
    use HasEnumsTrait;

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';

    /**
     * Get the color associated with this company role.
     */
    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::EDITOR => 'warning',
            self::VIEWER => 'secondary',
        };
    }

    /**
     * Get the CSS style class associated with this company role.
     */
    public function style(): string
    {
        return match ($this) {
            self::ADMIN => 'badge badge-light-danger',
            self::EDITOR => 'badge badge-light-warning',
            self::VIEWER => 'badge badge-light-secondary',
        };
    }

    /**
     * Check if the role is admin.
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if the role is editor.
     */
    public function isEditor(): bool
    {
        return $this === self::EDITOR;
    }

    /**
     * Check if the role is viewer.
     */
    public function isViewer(): bool
    {
        return $this === self::VIEWER;
    }
}
