<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Seeds one account per role so a fresh clone has something to sign in with.
 *
 * Credentials come from the environment. Nothing is hardcoded: the previous version of
 * this file committed a live BFP account with the password "bfp12345" to version control,
 * alongside commented-out admin and police accounts using "admin123".
 */
class UserSeeder extends Seeder
{
    /**
     * Only used outside production, and only when no password is configured.
     */
    private const LOCAL_FALLBACK_PASSWORD = 'password';

    public function run(): void
    {
        $municipality = Municipality::query()->orderBy('id')->first();
        $barangay = $municipality
            ? Barangay::where('municipality_id', $municipality->id)->orderBy('id')->first()
            : null;

        $accounts = [
            'admin' => 'ADMIN',
            'police' => 'POLICE',
            'bfp' => 'BFP',
            'user' => 'CITIZEN',
        ];

        foreach ($accounts as $role => $envPrefix) {
            $email = env("SEED_{$envPrefix}_EMAIL", "{$role}@ecoconnect.test");
            $password = env("SEED_{$envPrefix}_PASSWORD");

            if (blank($password)) {
                if (app()->isProduction()) {
                    throw new RuntimeException(
                        "SEED_{$envPrefix}_PASSWORD must be set before seeding in production."
                    );
                }

                $password = self::LOCAL_FALLBACK_PASSWORD;
            }

            User::updateOrCreate(
                ['email' => $email],
                [
                    'fname' => ucfirst($role),
                    'lname' => 'Account',
                    'phone' => '09000000000',
                    'password' => Hash::make($password),
                    'role' => $role,
                    'status' => 'Active',
                    'email_verified_at' => now(),
                    'municipality_id' => $municipality?->id,
                    'barangay_id' => $barangay?->id,
                ]
            );

            $this->command?->info("Seeded {$role} account: {$email}");
        }

        if (! app()->isProduction()) {
            $this->command?->warn(
                'Seeded accounts use the fallback password "'.self::LOCAL_FALLBACK_PASSWORD
                .'" unless SEED_*_PASSWORD is set. Never run this seeder against production data.'
            );
        }
    }
}
