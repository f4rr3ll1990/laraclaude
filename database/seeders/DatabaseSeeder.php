<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // A known admin account for obtaining API tokens (POST /api/login).
        User::updateOrCreate(
            ['email' => 'admin@f4x.test'],
            ['name' => 'F4X Admin', 'password' => Hash::make('password'), 'is_admin' => true],
        );

        $this->call(NewsSeeder::class);
    }
}
