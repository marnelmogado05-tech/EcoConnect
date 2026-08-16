<?php

namespace Database\Seeders;

use App\Models\Municipality;
use Illuminate\Database\Seeder;

/**
 * The seven Cagayan municipalities the platform covers. This list is the same one the
 * incident location validation checks against, so the two must stay in step.
 */
class MunicipalitySeeder extends Seeder
{
    public const MUNICIPALITIES = [
        'Sta Praxedes',
        'Claveria',
        'Sanchez Mira',
        'Pamplona',
        'Abulug',
        'Ballesteros',
        'Calayan',
    ];

    public function run(): void
    {
        foreach (self::MUNICIPALITIES as $name) {
            Municipality::firstOrCreate(['name' => $name]);
        }
    }
}
