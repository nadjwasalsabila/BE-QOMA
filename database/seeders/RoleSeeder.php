<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 'role_super_admin',  'name' => 'super_admin'],
            ['id' => 'role_owner',        'name' => 'owner'],
            ['id' => 'role_admin_cabang', 'name' => 'admin_cabang'],
            ['id' => 'role_kasir',        'name' => 'kasir'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }
}