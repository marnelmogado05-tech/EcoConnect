<?php

namespace Database\Factories;

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
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
        $type = fake()->randomElement(IncidentType::cases());

        return [
            'user_id' => User::factory(),
            'title' => $type->value.' - '.fake()->dateTime()->format('M j, Y g:i A'),
            'incident_type' => $type,
            'description' => fake()->paragraph(),
            'incident_date' => now()->toDateString(),
            'incident_time' => now()->format('H:i:s'),
            'status' => IncidentStatus::Pending,
            'priority' => IncidentPriority::Normal,
        ];
    }

    public function status(IncidentStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function priority(IncidentPriority $priority): static
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
            'status' => IncidentStatus::Assigned,
        ]);
    }
}
