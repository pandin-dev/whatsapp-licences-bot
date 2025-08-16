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

        User::factory()->create([
            'name' => 'Sherlon',
            'email' => 'sherlon@pandin.dev',
            'password' => bcrypt('pass1234'),
        ]);

        User::factory()->create([
            'name' => 'Pandin',
            'email' => 'pandin@pandin.dev',
            'password' => bcrypt('pandin'),
        ]);
    }
}
