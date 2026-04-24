<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Usaha;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Super Admin
        $superAdmin = User::updateOrCreate(['username' => 'superadmin'], [
            'id'       => Str::uuid(),
            'role_id'  => 'role_super_admin',
            'username' => 'superadmin',
            'password' => Hash::make('password123'),
            'email'    => 'superadmin@demo.com',
        ]);

        // 2. Buat Owner
        $owner = User::updateOrCreate(['username' => 'owner1'], [
            'id'       => Str::uuid(),
            'role_id'  => 'role_owner',
            'username' => 'owner1',
            'password' => Hash::make('password123'),
            'email'    => 'owner@demo.com',
        ]);

        // 3. Buat Usaha milik Owner
        $usaha = Usaha::updateOrCreate(['nama_usaha' => 'Warung Barokah'], [
            'id'         => Str::uuid(),
            'nama_usaha' => 'Warung Barokah',
            'owner_id'   => $owner->id,
            'email'      => 'warungbarokah@demo.com',
            'status' => true,
            'approved_at' => now(),
        ]);

        // Update owner agar punya usaha_id
        $owner->update(['usaha_id' => $usaha->id]);

        // Usaha pending untuk testing approval system
        $usahaPending = Usaha::updateOrCreate(['nama_usaha' => 'Kedai Baru Pending'], [
            'id'         => Str::uuid(),
            'nama_usaha' => 'Kedai Baru Pending',
            'owner_id'   => null,
            'email'      => 'kedaibaru@demo.com',
            'status'     => 'pending',
        ]);

        // 4. Buat 2 Cabang
        $cabang1 = Tenant::updateOrCreate(['nama_cabang' => 'Cabang Semarang Barat'], [
            'id'          => Str::uuid(),
            'usaha_id'    => $usaha->id,
            'nama_cabang' => 'Cabang Semarang Barat',
            'alamat'      => 'Jl. Siliwangi No. 12, Semarang',
            'status_buka' => true,
        ]);

        $cabang2 = Tenant::updateOrCreate(['nama_cabang' => 'Cabang Semarang Timur'], [
            'id'          => Str::uuid(),
            'usaha_id'    => $usaha->id,
            'nama_cabang' => 'Cabang Semarang Timur',
            'alamat'      => 'Jl. Pemuda No. 45, Semarang',
            'status_buka' => true,
        ]);

        // 5. Buat Kasir untuk tiap cabang
        User::updateOrCreate(['username' => 'kasir_cab1'], [
            'id'        => Str::uuid(),
            'role_id'   => 'role_kasir',
            'tenant_id' => $cabang1->id,
            'usaha_id'  => $usaha->id,
            'username'  => 'kasir_cab1',
            'password'  => Hash::make('password123'),
            'email'     => 'kasir1@demo.com',
        ]);

        User::updateOrCreate(['username' => 'kasir_cab2'], [
            'id'        => Str::uuid(),
            'role_id'   => 'role_kasir',
            'tenant_id' => $cabang2->id,
            'usaha_id'  => $usaha->id,
            'username'  => 'kasir_cab2',
            'password'  => Hash::make('password123'),
            'email'     => 'kasir2@demo.com',
        ]);

        // 6. Buat Admin Cabang
        User::updateOrCreate(['username' => 'admincabang1'], [
            'id'        => Str::uuid(),
            'role_id'   => 'role_admin_cabang',
            'tenant_id' => $cabang1->id,
            'usaha_id'  => $usaha->id,
            'username'  => 'admincabang1',
            'password'  => Hash::make('password123'),
            'email'     => 'admincabang@demo.com',
        ]);

        $this->command->info(' Seeder selesai! Summary:');
        $this->command->info("   Super Admin : superadmin / password123");
        $this->command->info("   Owner       : owner1 / password123");
        $this->command->info("   Admin Cab   : admincabang1 / password123");
        $this->command->info("   Kasir Cab1  : kasir_cab1 / password123");
        $this->command->info("   Kasir Cab2  : kasir_cab2 / password123");
    }
}