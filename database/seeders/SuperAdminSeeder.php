<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            'id'         => Str::uuid(),
            'role_id'    => 'superadmin',
            'usaha_id'   => null,
            'outlet_id'  => null,
            'username'   => 'superadmin',
            'email'      => 'superadmin@qoma.id',
            'password'   => Hash::make('superadmin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}