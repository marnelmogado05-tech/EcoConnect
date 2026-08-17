<?php

namespace App\Enums;

/**
 * Categories of environmental incident the platform accepts.
 *
 * The display mapping for these was copy-pasted into five controllers, and two of them
 * disagreed about the keys: the admin list used snake_case ('illegal_logging') while the
 * officer lists used the stored value ('Illegal Logging'), so one of the two type
 * filters could never match anything.
 */
enum IncidentType: string
{
    case IllegalLogging = 'Illegal Logging';
    case Pollution = 'Pollution';
    case WildlifeCrime = 'Wildlife Crime';
    case IllegalWasteDisposal = 'Illegal Waste Disposal';
    case Other = 'Other';

    public function label(): string
    {
        return $this === self::Other ? 'Other Environmental Incident' : $this->value;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->value], self::cases()),
            'label',
            'value'
        );
    }
}
