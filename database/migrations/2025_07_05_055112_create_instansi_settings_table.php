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
        Schema::create('instansi_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi')->nullable();
            $table->string('email_instansi')->nullable();
            $table->string('nomor_wa_instansi')->nullable();
            $table->text('alamat_instansi')->nullable();
            $table->string('logo_instansi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instansi_settings');
    }
};
