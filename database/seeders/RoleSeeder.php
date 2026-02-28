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
                'name' => 'admin_condominio',
                'description' => 'Administrador tenant del condominio.',
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
