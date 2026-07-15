<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'sales']);

        User::factory()->create([
            'name' => 'Administrador POS',
            'email' => 'admin@pos.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
        ]);
    }
}
