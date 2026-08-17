<?php

namespace App\Enums;

/**
 * The states an incident can occupy.
 *
 * The values match what is already stored in the database. They were previously held in
 * a column-level enum, which is what produced two of the blocking defects fixed in M1:
 * 'Assigned' was written by the assign handler but was never a permitted value, and a
 * new state could not be introduced without a schema migration. The column is a plain
 * string now and this enum is the constraint.
 */
enum IncidentStatus: string
{
    case Pending = 'Pending';
    case Assigned = 'Assigned';
    case InProgress = 'In Progress';
    case Resolved = 'Resolved';
    case Rejected = 'Rejected';

    /**
     * Human-readable name. Identical to the stored value today, but naming it separately
     * means the display text can change without rewriting the database.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Bootstrap contextual class for badges.
     *
     * This table was previously copy-pasted into five controllers as $statusBadgeClasses,
     * with different keys in some of them.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-warning',
            self::Assigned => 'bg-primary',
            self::InProgress => 'bg-info',
            self::Resolved => 'bg-success',
            self::Rejected => 'bg-danger',
        };
    }

    /**
     * States from which no further transition is allowed.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::Resolved, self::Rejected], true);
    }

    /**
     * Values suitable for a select list, keyed by stored value.
     *
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
