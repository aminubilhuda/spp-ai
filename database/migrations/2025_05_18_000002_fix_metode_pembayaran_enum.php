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
            $table->dropColumn('metode_pembayaran');
            $table->enum('metode_pembayaran', ['Bank Transfer', 'Cash'])->nullable()->after('bukti_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
            $table->enum('metode_pembayaran', ['tunai', 'transfer'])->nullable()->after('bukti_bayar');
        });
    }
};
