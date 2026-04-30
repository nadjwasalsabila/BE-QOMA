<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 'role_super_admin', 'name' => 'super_admin'],
            ['id' => 'role_owner',       'name' => 'owner'],
            ['id' => 'role_outlet',      'name' => 'outlet'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name']]
            );
        }

        $this->command->info('✅ RoleSeeder done: ' . DB::table('roles')->count() . ' roles');
    }
}