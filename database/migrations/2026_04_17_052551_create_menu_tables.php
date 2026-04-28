<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori menu
        Schema::create('kategori_menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('nama'); // makanan, minuman, snack, dessert, lainnya
        });

        // Bahan baku master (dibuat owner)
        Schema::create('bahan_master', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('harga_default', 15, 2);
            $table->string('gambar')->nullable();
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->cascadeOnDelete();
        });

        // Menu (dibuat owner, global untuk semua outlet)
        Schema::create('menu', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('kategori_id');
            $table->string('nama');
            $table->decimal('harga_default', 15, 2);
            $table->string('gambar')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->cascadeOnDelete();
            $table->foreign('kategori_id')->references('id')->on('kategori_menu');
        });

        // Relasi menu ↔ bahan_master (resep menu)
        Schema::create('menu_bahan', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('menu_id');
            $table->string('bahan_master_id');
            $table->decimal('jumlah_pakai', 10, 3);

            $table->foreign('menu_id')->references('id')->on('menu')->cascadeOnDelete();
            $table->foreign('bahan_master_id')->references('id')->on('bahan_master')->cascadeOnDelete();
        });

        // Menu per outlet (harga bisa beda dari default)
        Schema::create('menu_outlet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('menu_id');
            $table->string('outlet_id');
            $table->decimal('harga', 15, 2);
            $table->timestamps();

            $table->foreign('menu_id')->references('id')->on('menu')->cascadeOnDelete();
            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
        });

        // Add-on (tambahan menu)
        Schema::create('addon', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('usaha_id');
            $table->string('nama');
            $table->decimal('harga', 15, 2);
            $table->timestamps();

            $table->foreign('usaha_id')->references('id')->on('usaha')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addon');
        Schema::dropIfExists('menu_outlet');
        Schema::dropIfExists('menu_bahan');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('bahan_master');
        Schema::dropIfExists('kategori_menu');
    }
};