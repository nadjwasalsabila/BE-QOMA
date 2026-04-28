<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('role_id');
            $table->string('usaha_id')->nullable();
            $table->string('outlet_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('usaha_id')->references('id')->on('usaha')->nullOnDelete();
        });

        // Tambah FK owner_id ke usaha setelah users dibuat
        Schema::table('usaha', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });
        Schema::dropIfExists('users');
    }
};