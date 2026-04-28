<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 'superadmin', 'name' => 'superadmin'],
            ['id' => 'owner',      'name' => 'owner'],
            ['id' => 'outlet',     'name' => 'outlet'],
        ]);
    }
}