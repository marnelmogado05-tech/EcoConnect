<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::create([
        //     'fname' => 'Test',
        //     'lname' => 'Admin',
        //     'email' => 'marnelmogado05@gmail.com',
        //     'role' => 'admin',
        //     'password' => bcrypt('admin123'),
        // ]);

        // User::create([
        //     'fname' => 'Test',
        //     'lname' => 'Police',
        //     'email' => 'marnelmogado01@gmail.com',
        //     'role' => 'police',
        //     'password' => bcrypt('admin123'),
        // ]);

        // User::create([
        //     'fname' => 'Juan',
        //     'lname' => 'Dela Cruz',
        //     'phone' => '0912-345-6789',
        //     'email' => 'stapraxedes.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 1,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Pedro',
        //     'lname' => 'Garcia',
        //     'phone' => '0912-345-6790',
        //     'email' => 'claveria.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 2,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Miguel',
        //     'lname' => 'Reyes',
        //     'phone' => '0912-345-6791',
        //     'email' => 'sanchezmira.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 3,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Antonio',
        //     'lname' => 'Ramos',
        //     'phone' => '0912-345-6792',
        //     'email' => 'pamplona.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 4,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Carlos',
        //     'lname' => 'Mendoza',
        //     'phone' => '0912-345-6793',
        //     'email' => 'abulug.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 5,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Ricardo',
        //     'lname' => 'Santos',
        //     'phone' => '0912-345-6794',
        //     'email' => 'ballesteros.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 6,
        //     'role' => 'police'
        // ]);

        // User::create([
        //     'fname' => 'Fernando',
        //     'lname' => 'Gonzales',
        //     'phone' => '0912-345-6795',
        //     'email' => 'calayan.ps@ecoconnect.ph',
        //     'password' => bcrypt('police123'),
        //     'municipality_id' => 7,
        //     'role' => 'police'
        // ]);

        User::create([
            'fname' => 'Juan',
            'lname' => 'Johnson',
            'phone' => '0912-345-6796',
            'email' => 'calayan.bfp@ecoconnect.ph',
            'password' => bcrypt('bfp12345'),
            'municipality_id' => 7,
            'role' => 'bfp'
        ]);

    }
}
