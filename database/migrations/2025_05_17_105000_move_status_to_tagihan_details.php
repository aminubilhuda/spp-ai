<?php

namespace Database\Migrations;

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
        // Add status column to tagihan_details
        Schema::table('tagihan_details', function (Blueprint $table) {
            if (!Schema::hasColumn('tagihan_details', 'status')) {
                $table->enum('status', ['baru','angsur','lunas', 'belum_lunas'])->default('baru')->after('jumlah_biaya');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_details', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
