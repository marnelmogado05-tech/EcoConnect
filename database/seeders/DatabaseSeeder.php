<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Model events are suppressed during seeding. Creating a user with the citizen role
     * fires UserRegistered, which fans out push notifications to every staff account —
     * on a sync queue with no VAPID keys configured that aborts the seed.
     */
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MunicipalitySeeder::class,
            BarangaySeeder::class,
            UserSeeder::class,
        ]);
    }
}
