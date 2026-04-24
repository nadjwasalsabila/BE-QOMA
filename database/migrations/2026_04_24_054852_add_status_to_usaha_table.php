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
        Schema::table('usaha', function (Blueprint $table) {
            // Status usaha: pending → active → suspended → rejected
            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])
                ->default('pending')
                ->after('email');
            $table->text('catatan_admin')->nullable()->after('status'); // catatan super admin
            $table->timestamp('approved_at')->nullable()->after('catatan_admin');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->dropColumn(['status', 'catatan_admin', 'approved_at', 'rejected_at']);
        });
    }
};
