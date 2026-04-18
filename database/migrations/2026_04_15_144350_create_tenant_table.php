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
        Schema::create('tenant', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('nama_cabang');
            $table->string('alamat')->nullable();
            $table->boolean('status_buka')->default(true);
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant');
    }
};
