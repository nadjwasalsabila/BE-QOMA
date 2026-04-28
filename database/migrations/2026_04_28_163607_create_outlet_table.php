<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('nama_outlet');
            $table->string('alamat')->nullable();
            $table->string('email')->unique();
            $table->boolean('status_buka')->default(true);
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->cascadeOnDelete();
        });

        // Update users: FK outlet_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('outlet_id')->references('id')->on('outlet')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
        });
        Schema::dropIfExists('outlet');
    }
};