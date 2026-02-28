<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GlobalSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->updateOrCreate(
            ['name' => 'super_admin'],
            [
                'description' => 'Super administrador global sin condominio asignado.',
                'is_active' => true,
            ]
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'full_name' => 'Super Admin',
                'document_number' => '9000000001',
                'password' => Hash::make('12345678'),
                'is_active' => true,
            ]
        );

        // Regla de negocio: usuario global sin relación por condominio.
        DB::table('user_role')->where('user_id', $user->id)->delete();
    }
}

