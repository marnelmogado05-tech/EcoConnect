<?php

namespace App\Enums;

/**
 * How urgently an incident needs a response.
 *
 * The column-level enum this replaces was declared as ('Normal','High','Urgend') — a
 * typo. Every part of the application wrote 'Urgent', which strict mode rejected, so
 * raising an incident's priority was impossible until M1.
 */
enum IncidentPriority: string
{
    case Normal = 'Normal';
    case High = 'High';
    case Urgent = 'Urgent';

    public function label(): string
    {
        return $this->value;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Normal => 'bg-secondary',
            self::High => 'bg-warning',
            self::Urgent => 'bg-danger',
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
