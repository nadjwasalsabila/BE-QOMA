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
    Schema::create('menu_bahan_baku', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('menu_id');
        $table->string('bahan_baku_id');
        $table->decimal('jumlah_pakai', 12, 2)->default(0); // misal: 1 porsi nasi = 200gr beras
        $table->timestamps();

        $table->foreign('menu_id')->references('id')->on('menu')->onDelete('cascade');
        $table->foreign('bahan_baku_id')->references('id')->on('bahan_baku')->onDelete('cascade');

        // Pastikan kombinasi menu + bahan_baku tidak duplikat
        $table->unique(['menu_id', 'bahan_baku_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_bahan_baku');
    }
};
