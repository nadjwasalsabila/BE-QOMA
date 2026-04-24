<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('usaha_id')->nullable(); // untuk filter per usaha
            $table->string('tenant_id')->nullable(); // untuk filter per cabang
            $table->string('aktivitas');             // nama aksi: 'approve_usaha', 'reset_password', dll
            $table->text('deskripsi')->nullable();   // detail human-readable
            $table->json('metadata')->nullable();    // data tambahan (before/after, dll)
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
