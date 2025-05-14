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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->integer('wali_id')->nullable()->index();
            $table->string('wali_status')->nullable();
            $table->string('nama');
            $table->string('nisn')->unique();
            $table->string('nis')->unique();
            $table->string('foto')->nullable();
            $table->string('jenis_kelamin');
            $table->foreignId('jurusan_id')->nullable()->index();
            $table->string('kelas');
            $table->string('angkatan');
            $table->foreignId('user_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};