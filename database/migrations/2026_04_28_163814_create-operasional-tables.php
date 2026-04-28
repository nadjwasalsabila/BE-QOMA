<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meja per outlet
        Schema::create('meja', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('outlet_id');
            $table->string('nomor_meja');
            $table->string('qr_code')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
        });

        // Stok bahan baku per outlet
        Schema::create('bahan_outlet', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('outlet_id');
            $table->string('bahan_master_id');
            $table->decimal('stok', 10, 3)->default(0);
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
            $table->foreign('bahan_master_id')->references('id')->on('bahan_master')->cascadeOnDelete();
        });

        // Pesanan pelanggan
        Schema::create('pesanan', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('outlet_id');
            $table->string('meja_id');
            $table->string('nama_pelanggan');
            $table->string('no_telp');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, confirmed, paid, cancelled
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();

            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
            $table->foreign('meja_id')->references('id')->on('meja')->cascadeOnDelete();
        });

        // Detail item dalam pesanan
        Schema::create('pesanan_detil', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('pesanan_id');
            $table->string('menu_id');
            $table->integer('qty');
            $table->decimal('harga', 15, 2);

            $table->foreign('pesanan_id')->references('id')->on('pesanan')->cascadeOnDelete();
            $table->foreign('menu_id')->references('id')->on('menu')->cascadeOnDelete();
        });

        // Add-on per item pesanan
        Schema::create('pesanan_addon', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('pesanan_detil_id');
            $table->string('addon_id');
            $table->integer('qty')->default(1);

            $table->foreign('pesanan_detil_id')->references('id')->on('pesanan_detil')->cascadeOnDelete();
            $table->foreign('addon_id')->references('id')->on('addon')->cascadeOnDelete();
        });

        // Pembayaran
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('pesanan_id')->unique();
            $table->string('metode')->nullable(); // cash, transfer, dll
            $table->decimal('jumlah_bayar', 15, 2);
            $table->string('status')->default('pending'); // pending, paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('pesanan_id')->references('id')->on('pesanan')->cascadeOnDelete();
        });

        // Pengeluaran per outlet (pembelian bahan baku)
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('outlet_id');
            $table->decimal('total', 15, 2);
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
        });

        // Kerugian per outlet
        Schema::create('kerugian', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('outlet_id');
            $table->decimal('total_rugi', 15, 2);
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlet')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerugian');
        Schema::dropIfExists('pengeluaran');
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('pesanan_addon');
        Schema::dropIfExists('pesanan_detil');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('bahan_outlet');
        Schema::dropIfExists('meja');
    }
};