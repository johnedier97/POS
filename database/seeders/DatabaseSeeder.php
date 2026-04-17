<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        \App\Models\Role::firstOrCreate(['name' => 'sales']);

        User::factory()->create([
            'name' => 'Administrador POS',
            'email' => 'admin@pos.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
        ]);
    }
}
