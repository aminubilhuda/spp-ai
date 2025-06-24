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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans')->onDelete('cascade');
            $table->foreignId('tagihan_detail_id')->nullable()->constrained('tagihan_details')->onDelete('cascade');
            $table->foreignId('wali_id')->index();
            $table->decimal('jumlah_dibayar', 15, 2);
            $table->date('tanggal_bayar')->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->enum('metode_pembayaran', ['Bank Transfer', 'Cash'])->nullable();
            $table->enum('status_konfirmasi', ['Belum Dikonfirmasi', 'Sudah Dikonfirmasi'])->nullable();
            $table->foreignId('bank_sekolah_id')->nullable()->constrained('bank_sekolahs')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};