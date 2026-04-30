<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,  // ← HARUS PERTAMA
            PlanSeeder::class,  // ← HARUS SEBELUM UserSeeder
            UserSeeder::class,  // ← TERAKHIR
        ]);
    }
}