<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plans')->updateOrInsert(
            ['id' => 'plan_free_trial'],
            [
                'id'           => 'plan_free_trial',
                'nama_plan'    => 'Free Trial',
                'harga'        => 0,
                'batas_outlet' => 2,
                'deskripsi'    => 'Gratis, maksimal 2 outlet',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        );

        DB::table('plans')->updateOrInsert(
            ['id' => 'plan_pro'],
            [
                'id'           => 'plan_pro',
                'nama_plan'    => 'Pro',
                'harga'        => 299000,
                'batas_outlet' => -1,
                'deskripsi'    => 'Unlimited outlet, semua fitur aktif',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        );

        $this->command->info('✅ PlanSeeder done: ' . DB::table('plans')->count() . ' plans');
    }
}