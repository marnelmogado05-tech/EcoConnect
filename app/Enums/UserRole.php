<?php

namespace App\Enums;

/**
 * Who someone is in the system.
 *
 * Note the citizen role is stored as 'user', not 'citizen'. AdminCitizenController
 * counted role = 'citizen' — a value that appears nowhere else — so its active and
 * suspended totals read zero permanently until M1.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Police = 'police';
    case Bfp = 'bfp';
    case Citizen = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Police => 'Police',
            self::Bfp => 'Bureau of Fire Protection',
            self::Citizen => 'Citizen',
        };
    }

    /**
     * Roles that respond to incidents assigned to them.
     *
     * @return array<int, self>
     */
    public static function responders(): array
    {
        return [self::Police, self::Bfp];
    }

    public function isResponder(): bool
    {
        return in_array($this, self::responders(), true);
    }

    /**
     * Responder roles as stored values, for whereIn() clauses.
     *
     * @return array<int, string>
     */
    public static function responderValues(): array
    {
        return array_map(fn (self $role) => $role->value, self::responders());
    }
}
