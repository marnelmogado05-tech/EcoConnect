<?php

namespace App\Enums;

/**
 * Whether an account may act.
 *
 * These values were written as 'active', 'Active' and 'Suspended' from different call
 * sites. MySQL's case-insensitive collation hid most of it, but the suspension check in
 * the report handler was a strict PHP comparison against 'suspended' — so suspended
 * accounts could carry on filing reports until M1 normalised the casing.
 */
enum UserStatus: string
{
    case Active = 'Active';
    case Suspended = 'Suspended';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-success',
            self::Suspended => 'bg-danger',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases()),
            'label',
            'value'
        );
    }
}
