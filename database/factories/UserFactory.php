<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Models\Municipality;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fname' => fake()->firstName(),
            'mname' => fake()->optional(0.4)->firstName(),
            'lname' => fake()->lastName(),
            'extname' => null,
            'phone' => fake()->numerify('09#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'status' => 'Active',
            'municipality_id' => null,
            'barangay_id' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Suspended',
        ]);
    }

    /**
     * Give the user a role other than the default citizen role.
     */
    public function role(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    public function admin(): static
    {
        return $this->role('admin');
    }

    public function police(): static
    {
        return $this->role('police');
    }

    public function bfp(): static
    {
        return $this->role('bfp');
    }

    /**
     * Attach the user to a municipality and one of its barangays.
     */
    public function located(?Municipality $municipality = null): static
    {
        return $this->state(function (array $attributes) use ($municipality) {
            $municipality ??= Municipality::inRandomOrder()->first()
                ?? Municipality::factory()->create();

            return [
                'municipality_id' => $municipality->id,
                'barangay_id' => Barangay::where('municipality_id', $municipality->id)
                    ->inRandomOrder()
                    ->value('id'),
            ];
        });
    }
}
