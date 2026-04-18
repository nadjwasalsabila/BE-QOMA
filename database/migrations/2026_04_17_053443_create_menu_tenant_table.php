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
    Schema::create('menu_tenant', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('menu_id');
        $table->string('tenant_id');
        $table->decimal('harga', 12, 2)->default(0); // bisa override dari harga_default
        $table->boolean('is_available')->default(true); // admin cabang bisa sembunyikan menu
        $table->timestamps();

        $table->foreign('menu_id')->references('id')->on('menu')->onDelete('cascade');
        $table->foreign('tenant_id')->references('id')->on('tenant')->onDelete('cascade');

        // 1 menu hanya 1 record per tenant
        $table->unique(['menu_id', 'tenant_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_tenant');
    }
};
