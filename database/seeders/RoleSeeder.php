<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'super administrador'],
            [
                'description' => 'Rol con acceso total al sistema.',
                'is_active' => true,
            ]
        );
    }
}

