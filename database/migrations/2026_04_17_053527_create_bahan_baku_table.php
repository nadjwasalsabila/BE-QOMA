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
    Schema::create('bahan_baku', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('tenant_id');
        $table->string('nama');
        $table->decimal('stok', 12, 2)->default(0);
        $table->string('satuan')->nullable();        // pcs, kg, liter, dll
        $table->date('tgl_masuk')->nullable();
        $table->date('tgl_kadaluarsa')->nullable();
        $table->string('gambar')->nullable();
        $table->timestamps();

        $table->foreign('tenant_id')->references('id')->on('tenant')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
