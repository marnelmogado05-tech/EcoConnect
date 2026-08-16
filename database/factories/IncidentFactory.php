<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * reference_number is intentionally omitted — IncidentObserver assigns it.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(array_keys(Incident::getIncidentTypes()));

        return [
            'user_id' => User::factory(),
            'title' => $type.' - '.fake()->dateTime()->format('M j, Y g:i A'),
            'incident_type' => $type,
            'description' => fake()->paragraph(),
            'incident_date' => now()->toDateString(),
            'incident_time' => now()->format('H:i:s'),
            'status' => Incident::STATUS_PENDING,
            'priority' => Incident::PRIORITY_NORMAL,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function priority(string $priority): static
    {
        return $this->state(fn (array $attributes) => ['priority' => $priority]);
    }

    /**
     * An incident already assigned to a responding officer.
     */
    public function assignedTo(User $officer): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $officer->id,
            'assigned_at' => now(),
            'status' => 'Assigned',
        ]);
    }
}
