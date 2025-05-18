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
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('status_konfirmasi');
            $table->enum('status_konfirmasi', ['Belum Dikonfirmasi', 'Sudah Dikonfirmasi'])->nullable()->after('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('status_konfirmasi');
            $table->enum('status_konfirmasi', ['belum', 'sudah'])->nullable()->after('metode_pembayaran');
        });
    }
};
