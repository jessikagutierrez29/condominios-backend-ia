<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super administrador',
                'description' => 'Rol con acceso total al sistema.',
            ],
            [
                'name' => 'Seguridad',
                'description' => 'Personal operativo de control y vigilancia.',
            ],
            [
                'name' => 'Aseo',
                'description' => 'Personal operativo de limpieza.',
            ],
            [
                'name' => 'Mantenimiento',
                'description' => 'Personal operativo de mantenimiento.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
