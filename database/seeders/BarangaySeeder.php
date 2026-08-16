<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\Municipality;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER DATA — NOT THE OFFICIAL BARANGAY LIST.
 *
 * Registration requires the user to pick a barangay, so a local environment needs some
 * rows to select from. These names are generic on purpose: inventing plausible-looking
 * official place names for a government system is worse than an obvious placeholder,
 * because the fake ones would survive into production unnoticed.
 *
 * Before deploying, replace this with the authoritative PSGC barangay list for the seven
 * covered municipalities and import it as reference data rather than a seeder.
 */
class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Poblacion', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5'];

        Municipality::all()->each(function (Municipality $municipality) use ($names) {
            foreach ($names as $name) {
                Barangay::firstOrCreate([
                    'municipality_id' => $municipality->id,
                    'name' => $name,
                ]);
            }
        });
    }
}
