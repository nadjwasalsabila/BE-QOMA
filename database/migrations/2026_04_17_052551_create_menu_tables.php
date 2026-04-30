<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('kategori_id');
            $table->string('nama');
            $table->decimal('harga_default', 12, 2)->default(0);
            $table->string('gambar')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->onDelete('cascade');
            $table->foreign('kategori_id')->references('id')->on('kategori_menu')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};