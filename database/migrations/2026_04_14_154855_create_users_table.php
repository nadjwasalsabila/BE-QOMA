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
    Schema::create('users', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('role_id')->nullable();
        $table->string('tenant_id')->nullable();
        $table->string('usaha_id')->nullable();
        $table->string('username')->unique();
        $table->string('password');
        $table->string('email')->nullable();
        $table->timestamps();

        $table->foreign('role_id')->references('id')->on('roles');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
